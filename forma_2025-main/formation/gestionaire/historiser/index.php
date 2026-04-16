<?php
require_once __DIR__ . '/../../../include/protection.php';
protect([2], strict: true);
require_once __DIR__ . '/../../../include/bd.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (isset($_GET['export']) && $_GET['export'] === '1') {
    $rows = $pdo->query('SELECT s.date_de_session, f.labell AS formation, u.nom, u.prenom, u.email, i.etat FROM session s JOIN formation f ON s.id_formation = f.id JOIN inscription i ON i.id_session = s.id JOIN utilisateur u ON i.id_utilisateur = u.id WHERE s.date_de_session < CURDATE() ORDER BY s.date_de_session DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="historique_inscriptions.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Date session', 'Formation', 'Nom', 'Prénom', 'Email', 'État']);
    foreach ($rows as $row) {
        fputcsv($output, [$row['date_de_session'], $row['formation'], $row['nom'], $row['prenom'], $row['email'], $row['etat']]);
    }
    exit();
}

$rows = $pdo->query('SELECT s.date_de_session, f.labell AS formation, u.nom, u.prenom, u.email, i.etat FROM session s JOIN formation f ON s.id_formation = f.id JOIN inscription i ON i.id_session = s.id JOIN utilisateur u ON i.id_utilisateur = u.id WHERE s.date_de_session < CURDATE() ORDER BY s.date_de_session DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Historique des inscriptions</h1>
                <p class="text-sm text-gray-500">Consultez les inscriptions aux sessions passées et exportez-les au format CSV.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour gestionnaire</a>
                <a href="?export=1" class="inline-flex items-center rounded-full bg-m2l-primary px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">Exporter CSV</a>
                <a href="../../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>
        <div class="overflow-x-auto bg-white border border-gray-200 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Session</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Formation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Participant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">État</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Aucune inscription historique disponible.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm text-gray-900"><?= h($row['date_de_session']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['formation']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-900"><?= h($row['prenom'] . ' ' . $row['nom']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['email']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['etat']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
