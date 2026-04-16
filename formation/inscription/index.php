<?php
require_once __DIR__ . '/../../include/protection.php';
protect([1, 2], strict: true);
require_once __DIR__ . '/../../include/bd.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (!user_has_status([1, 2])) {
    header('Location: ../?erreur=' . urlencode('Statut non autorisé pour l\'inscription'));
    exit();
}

$userId = $_SESSION['id'];
$message = ''; $errors = [];

$sessionId = (int)($_POST['session_id'] ?? $_GET['session_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($sessionId <= 0) {
        $errors[] = 'Sélectionnez une session valide.';
    } else {
        $session = $pdo->prepare('SELECT s.*, f.id AS formation_id, f.labell, f.max_participant, df.id_domaine FROM session s JOIN formation f ON s.id_formation = f.id LEFT JOIN dom_forma df ON f.id = df.id WHERE s.id = :id AND s.date_de_session >= CURDATE() AND s.date_limite >= CURDATE()');
        $session->execute([':id' => $sessionId]);
        $session = $session->fetch();
        if (!$session) {
            $errors[] = 'Session invalide ou déjà passée.';
        }
    }

    if (empty($errors)) {
        $already = $pdo->prepare('SELECT COUNT(*) FROM inscription WHERE id_utilisateur = :user AND id_session = :session');
        $already->execute([':user' => $userId, ':session' => $sessionId]);
        if ($already->fetchColumn() > 0) {
            $errors[] = 'Vous êtes déjà inscrit à cette session.';
        }
    }

    if (empty($errors)) {
        $year = date('Y', strtotime($session['date_de_session']));
        $countYear = $pdo->prepare('SELECT COUNT(*) FROM inscription i JOIN session s ON i.id_session = s.id WHERE i.id_utilisateur = :user AND YEAR(s.date_de_session) = :year');
        $countYear->execute([':user' => $userId, ':year' => $year]);
        if ($countYear->fetchColumn() >= 3) {
            $errors[] = 'Vous avez déjà 3 inscriptions pour cette année.';
        }
    }

    if (empty($errors)) {
        $countDomain = $pdo->prepare('SELECT COUNT(*) FROM inscription i JOIN session s ON i.id_session = s.id JOIN formation f ON s.id_formation = f.id JOIN dom_forma df ON f.id = df.id WHERE i.id_utilisateur = :user AND YEAR(s.date_de_session) = :year AND df.id_domaine = :domaine');
        $countDomain->execute([':user' => $userId, ':year' => $year, ':domaine' => $session['id_domaine'] ?? 0]);
        if ($countDomain->fetchColumn() >= 2) {
            $errors[] = 'Vous avez déjà 2 inscriptions dans ce domaine.';
        }
    }

    if (empty($errors)) {
        $countPlaces = $pdo->prepare('SELECT COUNT(*) FROM inscription WHERE id_session = :session AND etat IN ("enregistré", "validé")');
        $countPlaces->execute([':session' => $sessionId]);
        if ($countPlaces->fetchColumn() >= $session['max_participant']) {
            $errors[] = 'La session est complète.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO inscription (id_utilisateur, id_session, etat) VALUES (:user, :session, "enregistré")');
        $stmt->execute([':user' => $userId, ':session' => $sessionId]);
        $message = 'Votre inscription a été enregistrée. Elle sera validée après réception du règlement.';
    }
}

$registeredSessionsStmt = $pdo->prepare('SELECT id_session FROM inscription WHERE id_utilisateur = :user');
$registeredSessionsStmt->execute([':user' => $userId]);
$registeredSessionIds = array_column($registeredSessionsStmt->fetchAll(), 'id_session');

$sessions = $pdo->query('SELECT s.id, s.date_de_session, s.date_limite, f.labell, f.max_participant, GROUP_CONCAT(d.labell SEPARATOR ", ") AS domaines, COUNT(i.id_session) AS inscriptions_count FROM session s JOIN formation f ON s.id_formation = f.id LEFT JOIN dom_forma df ON f.id = df.id LEFT JOIN domaine d ON df.id_domaine = d.id_domaine LEFT JOIN inscription i ON s.id = i.id_session AND i.etat IN ("enregistré", "validé") WHERE s.date_de_session >= CURDATE() AND s.date_limite >= CURDATE() GROUP BY s.id, s.date_de_session, s.date_limite, f.labell, f.max_participant ORDER BY s.date_de_session ASC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inscription à une formation</h1>
                <p class="text-sm text-gray-500">Choisissez une session à venir et inscrivez-vous.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour Formation</a>
                <a href="../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="mb-8 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <form method="post" class="space-y-4">
                <label class="block text-sm text-gray-700">
                    <span>Session</span>
                    <select name="session_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Sélectionnez une session</option>
                        <?php foreach ($sessions as $session): ?>
                        <?php $isRegistered = in_array($session['id'], $registeredSessionIds, true); ?>
                        <option value="<?= (int)$session['id'] ?>" <?= $sessionId === (int)$session['id'] ? 'selected' : '' ?> <?= $isRegistered ? 'disabled' : '' ?>><?= h($session['labell']) ?> — <?= h($session['date_de_session']) ?> — date limite <?= h($session['date_limite']) ?> (<?= h($session['domaines']) ?>, places <?= (int)$session['inscriptions_count'] ?>/<?= (int)$session['max_participant'] ?>)<?= $isRegistered ? ' — déjà inscrit' : '' ?></option>
                    <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="rounded-full bg-m2l-primary px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">S'inscrire</button>
            </form>
        </section>

        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Prochaines sessions ouvertes</h2>
            <div class="space-y-4">
                <?php if (empty($sessions)): ?>
                    <p class="text-gray-500">Aucune session à venir disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <?php $isRegistered = in_array($session['id'], $registeredSessionIds, true); ?>
                        <div class="rounded-2xl border border-gray-200 p-4 bg-gray-50">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?= h($session['labell']) ?></h3>
                                    <p class="text-sm text-gray-600">Session le <?= h($session['date_de_session']) ?> — <?= h($session['domaines']) ?></p>
                                    <p class="text-sm text-gray-500">Date limite d'inscription : <?= h($session['date_limite']) ?></p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <p class="text-sm text-gray-700">Places : <?= (int)$session['inscriptions_count'] ?>/<?= (int)$session['max_participant'] ?></p>
                                    <?php if ($isRegistered): ?>
                                        <span class="rounded-full bg-gray-200 px-4 py-2 text-xs font-semibold text-gray-700">Déjà inscrit</span>
                                    <?php else: ?>
                                        <button type="button" onclick="document.querySelector('select[name=session_id]').value='<?= (int)$session['id'] ?>'; document.querySelector('form').submit();" class="rounded-full bg-m2l-primary px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-600">S'inscrire</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
