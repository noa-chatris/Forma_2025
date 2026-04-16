<?php
require_once __DIR__ . '/../../../include/protection.php';
protect([2], strict: true);
require_once __DIR__ . '/../../../include/bd.php';
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_utilisateur'], $_POST['id_session'])) {
    $stmt = $pdo->prepare('UPDATE inscription SET etat = "validé" WHERE id_utilisateur = :user AND id_session = :session');
    $stmt->execute([':user' => (int)$_POST['id_utilisateur'], ':session' => (int)$_POST['id_session']]);
    $message = 'Règlement enregistré et inscription validée.';
}

$rows = $pdo->query('SELECT i.id_utilisateur, i.id_session, i.etat, u.nom, u.prenom, u.email, s.date_de_session, f.labell FROM inscription i JOIN utilisateur u ON i.id_utilisateur = u.id JOIN session s ON i.id_session = s.id JOIN formation f ON s.id_formation = f.id WHERE i.etat = "enregistré" ORDER BY s.date_de_session ASC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Enregistrer un règlement</h1>
                <p class="text-sm text-gray-500">Validez les inscriptions enregistrées.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour gestionnaire</a>
                <a href="../../../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>
        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700"><?= h($message) ?></div>
        <?php endif; ?>
        <div class="overflow-x-auto bg-white border border-gray-200 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Participant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Formation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Session</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Aucune inscription en attente de validation.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm text-gray-900"><?= h($row['prenom'] . ' ' . $row['nom']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['labell']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['date_de_session']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['email']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <form method="post" class="inline-block">
                                        <input type="hidden" name="id_utilisateur" value="<?= (int)$row['id_utilisateur'] ?>">
                                        <input type="hidden" name="id_session" value="<?= (int)$row['id_session'] ?>">
                                        <button type="submit" class="rounded-full bg-m2l-primary px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-600">Valider le règlement</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
