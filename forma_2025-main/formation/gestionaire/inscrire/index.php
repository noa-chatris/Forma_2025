<?php
require_once __DIR__ . '/../../../include/protection.php';
protect([2], strict: true);
require_once __DIR__ . '/../../../include/bd.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$userId = (int)($_POST['user_id'] ?? 0);
$formationId = (int)($_POST['formation_id'] ?? $_GET['formation_id'] ?? 0);
$sessionId = (int)($_POST['session_id'] ?? $_GET['session_id'] ?? 0);
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userId <= 0) {
        $errors[] = 'Sélectionnez un utilisateur.';
    } else {
        $userStmt = $pdo->prepare('SELECT id, nom, prenom, email, id_role FROM utilisateur WHERE id = :id');
        $userStmt->execute([':id' => $userId]);
        $user = $userStmt->fetch();
        if (!$user) {
            $errors[] = 'Utilisateur introuvable.';
        }
    }

    if ($sessionId <= 0) {
        $errors[] = 'Sélectionnez une session valide.';
    } else {
        $sessionStmt = $pdo->prepare('SELECT s.*, f.id AS formation_id, f.labell, f.max_participant, df.id_domaine FROM session s JOIN formation f ON s.id_formation = f.id LEFT JOIN dom_forma df ON f.id = df.id WHERE s.id = :id AND s.date_de_session >= CURDATE() AND s.date_limite >= CURDATE()');
        $sessionStmt->execute([':id' => $sessionId]);
        $session = $sessionStmt->fetch();
        if (!$session) {
            $errors[] = 'Session invalide ou déjà passée.';
        }
    }

    if (empty($errors)) {
        $already = $pdo->prepare('SELECT COUNT(*) FROM inscription WHERE id_utilisateur = :user AND id_session = :session');
        $already->execute([':user' => $userId, ':session' => $sessionId]);
        if ($already->fetchColumn() > 0) {
            $errors[] = 'Cet utilisateur est déjà inscrit à cette session.';
        }
    }

    if (empty($errors)) {
        $year = date('Y', strtotime($session['date_de_session']));
        $countYear = $pdo->prepare('SELECT COUNT(*) FROM inscription i JOIN session s ON i.id_session = s.id WHERE i.id_utilisateur = :user AND YEAR(s.date_de_session) = :year');
        $countYear->execute([':user' => $userId, ':year' => $year]);
        if ($countYear->fetchColumn() >= 3) {
            $errors[] = 'L\'utilisateur a déjà 3 inscriptions pour cette année.';
        }
    }

    if (empty($errors)) {
        $countDomain = $pdo->prepare('SELECT COUNT(*) FROM inscription i JOIN session s ON i.id_session = s.id JOIN formation f ON s.id_formation = f.id JOIN dom_forma df ON f.id = df.id WHERE i.id_utilisateur = :user AND YEAR(s.date_de_session) = :year AND df.id_domaine = :domaine');
        $countDomain->execute([':user' => $userId, ':year' => $year, ':domaine' => $session['id_domaine'] ?? 0]);
        if ($countDomain->fetchColumn() >= 2) {
            $errors[] = 'L\'utilisateur a déjà 2 inscriptions dans ce domaine cette année.';
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
        $message = 'Inscription de l\'utilisateur enregistrée. Elle sera validée après réception du règlement.';
    }
}

$users = $pdo->query('SELECT id, nom, prenom, email, id_role FROM utilisateur ORDER BY nom, prenom')->fetchAll();
$sql = 'SELECT s.id, s.date_de_session, s.date_limite, f.labell, f.max_participant, GROUP_CONCAT(d.labell SEPARATOR ", ") AS domaines, COUNT(i.id_session) AS inscriptions_count FROM session s JOIN formation f ON s.id_formation = f.id LEFT JOIN dom_forma df ON f.id = df.id LEFT JOIN domaine d ON df.id_domaine = d.id_domaine LEFT JOIN inscription i ON s.id = i.id_session AND i.etat IN ("enregistré", "validé") WHERE s.date_de_session >= CURDATE() AND s.date_limite >= CURDATE()';
$params = [];
if ($formationId > 0) {
    $sql .= ' AND f.id = :formation_id';
    $params[':formation_id'] = $formationId;
}
$sql .= ' GROUP BY s.id, s.date_de_session, s.date_limite, f.labell, f.max_participant ORDER BY s.date_de_session ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inscrire un utilisateur</h1>
                <p class="text-sm text-gray-500">Inscrivez un utilisateur à une session à venir.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour espace gestionnaire</a>
                <a href="../../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="mb-8 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <form method="post" class="grid gap-6">
                <?php if ($formationId > 0): ?>
                    <?php $formationStmt = $pdo->prepare('SELECT labell FROM formation WHERE id = :id'); $formationStmt->execute([':id' => $formationId]); $formation = $formationStmt->fetch(); ?>
                    <?php if ($formation): ?>
                        <label class="block text-sm text-gray-700">
                            <span>Formation</span>
                            <input type="text" value="<?= h($formation['labell']) ?>" disabled class="mt-1 block w-full rounded-xl border-gray-300 bg-gray-100 text-gray-700 px-4 py-2">
                        </label>
                    <?php endif; ?>
                <?php endif; ?>
                <label class="block text-sm text-gray-700">
                    <span>Utilisateur</span>
                    <select name="user_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Sélectionnez un utilisateur</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int)$user['id'] ?>" <?= $userId === (int)$user['id'] ? 'selected' : '' ?>><?= h($user['nom'] . ' ' . $user['prenom'] . ' (' . $user['email'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block text-sm text-gray-700">
                    <span>Session</span>
                    <select name="session_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Sélectionnez une session</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= (int)$session['id'] ?>" <?= $sessionId === (int)$session['id'] ? 'selected' : '' ?>><?= h($session['labell']) ?> — <?= h($session['date_de_session']) ?> (<?= h($session['domaines']) ?>, places <?= (int)$session['inscriptions_count'] ?>/<?= (int)$session['max_participant'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button type="submit" class="rounded-full bg-m2l-primary px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">Enregistrer l'inscription</button>
            </form>
        </section>

        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Sessions à venir</h2>
            <div class="space-y-4">
                <?php if (empty($sessions)): ?>
                    <p class="text-gray-500">Aucune session à venir disponible.</p>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?= h($session['labell']) ?></h3>
                                    <p class="text-sm text-gray-600">Session le <?= h($session['date_de_session']) ?> — <?= h($session['domaines']) ?></p>
                                </div>
                                <p class="text-sm text-gray-700">Places : <?= (int)$session['inscriptions_count'] ?>/<?= (int)$session['max_participant'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>
