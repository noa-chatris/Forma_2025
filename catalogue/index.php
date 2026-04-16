<?php
require_once __DIR__ . '/../include/catch_get_error.php';
require_once __DIR__ . '/../include/protection.php';
protect([2, 3], strict: true);
require_once __DIR__ . '/../include/bd.php';

$sql = "SELECT f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant, GROUP_CONCAT(d.labell SEPARATOR ', ') AS domaines,
    COUNT(s.id) AS sessions_count
FROM formation f
LEFT JOIN dom_forma df ON f.id = df.id
LEFT JOIN domaine d ON df.id_domaine = d.id_domaine
LEFT JOIN session s ON f.id = s.id_formation
GROUP BY f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant
ORDER BY f.labell ASC";
$result = $pdo->query($sql)->fetchAll();

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$current = current_user();
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Catalogue des formations</h1>
                <p class="text-sm text-gray-500">Consultez les formations disponibles et retrouvez toutes les sessions.</p>
                <?php if (!empty($current['prenom']) || !empty($current['nom'])): ?>
                    <p class="text-sm text-gray-500">Connecté(e) : <?= h(trim(($current['prenom'] ?? '') . ' ' . ($current['nom'] ?? ''))) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Accueil</a>
                <a href="../formation/" class="inline-flex items-center rounded-full bg-m2l-primary px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">Voir la page Formation</a>
                <?php if (!empty($current['id_role']) && (int) $current['id_role'] === 2): ?>
                    <a href="../formation/gestionaire/" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Gestionnaire</a>
                <?php endif; ?>
                <?php if ((int) ($_SESSION['id_role'] ?? $current['id_role'] ?? 0) === 2): ?>
                    <a href="modification/" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Gérer le catalogue</a>
                <?php endif; ?>
                <a href="../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        <?php if (error()): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700"><?= error() ?></div>
        <?php endif; ?>
        </header>

        <div class="overflow-x-auto bg-white border border-gray-200 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Formation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Domaines</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Objectifs</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Coût (€)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Public</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Max</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sessions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($result)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Aucune formation disponible.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($result as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm font-medium text-gray-900"><?= h($row['labell']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['domaines']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['objectifs']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['cout']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['public']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['max_participant']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['sessions_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
