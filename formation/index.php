<?php
require_once __DIR__ . '/../include/catch_get_error.php';
require_once __DIR__ . '/../include/protection.php';
protect();
require_once __DIR__ . '/../include/bd.php';

$NomForma = trim($_GET['NomForma'] ?? '');
$tri = trim($_GET['tri'] ?? '');

$sql = "SELECT f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant, GROUP_CONCAT(d.labell SEPARATOR ', ') AS domaines,
    COUNT(s.id) AS sessions_count, MIN(CASE WHEN s.date_de_session >= CURDATE() THEN s.date_de_session END) AS prochain_session,
    (SELECT s2.id FROM session s2 WHERE s2.id_formation = f.id AND s2.date_de_session >= CURDATE() ORDER BY s2.date_de_session ASC LIMIT 1) AS prochain_session_id
FROM formation f
LEFT JOIN dom_forma df ON f.id = df.id
LEFT JOIN domaine d ON df.id_domaine = d.id_domaine
LEFT JOIN session s ON f.id = s.id_formation
WHERE 1 = 1";
$params = [];

if ($NomForma !== '') {
    $sql .= " AND f.labell LIKE :labell";
    $params[':labell'] = "%" . $NomForma . "%";
}

$sql .= " GROUP BY f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant";

$allowedSort = [
    'titre_asc' => 'f.labell ASC',
    'titre_desc' => 'f.labell DESC',
];

if (array_key_exists($tri, $allowedSort) && $tri !== '') {
    $sql .= " ORDER BY " . $allowedSort[$tri];
} else {
    $sql .= " ORDER BY f.labell ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$current = current_user();
$alreadyRegisteredFormationIds = [];
if (!empty($current['id']) && isset($current['id_role']) && (int)$current['id_role'] === 1) {
    $stmtRegistered = $pdo->prepare('SELECT DISTINCT s.id_formation FROM inscription i JOIN session s ON i.id_session = s.id WHERE i.id_utilisateur = :user AND s.date_de_session >= CURDATE() AND i.etat IN ("enregistré", "validé")');
    $stmtRegistered->execute([':user' => $current['id']]);
    $alreadyRegisteredFormationIds = array_column($stmtRegistered->fetchAll(), 'id_formation');
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion des formations</h1>
                <p class="text-sm text-gray-500">Consultez les formations et accédez à l’inscription.</p>
                <?php if (!empty($current['prenom']) || !empty($current['nom'])): ?>
                    <p class="text-sm text-gray-500">Connecté(e) : <?= h(trim(($current['prenom'] ?? '') . ' ' . ($current['nom'] ?? ''))) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Accueil</a>
                <?php if (!empty($current['id_role']) && (int) $current['id_role'] === 2): ?>
                    <a href="gestionaire/" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Gestionnaire</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['id_role']) && in_array((int) $_SESSION['id_role'], [2, 3], true)): ?>
                    <a href="../catalogue/" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Voir le catalogue</a>
                <?php endif; ?>
                <a href="../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>
        <?php if (error()): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700"><?= error() ?></div>
        <?php endif; ?>
        <section class="mb-8 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <form method="get" action="" class="grid gap-4 sm:grid-cols-3">
                <label class="block text-sm text-gray-700">
                    <span>Titre contient</span>
                    <input type="text" name="NomForma" value="<?= h($NomForma) ?>" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label class="block text-sm text-gray-700">
                    <span>Tri</span>
                    <select name="tri" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Ordre</option>
                        <option value="titre_asc" <?= $tri === 'titre_asc' ? 'selected' : '' ?>>Titre A → Z</option>
                        <option value="titre_desc" <?= $tri === 'titre_desc' ? 'selected' : '' ?>>Titre Z → A</option>
                    </select>
                </label>
                <div class="flex items-end gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-m2l-primary px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600">Rechercher</button>
                </div>
            </form>
        </section>

        <section class="overflow-x-auto bg-white border border-gray-200 rounded-2xl shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Formation</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Domaines</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Objectifs</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Coût</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Public</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Sessions</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Prochaine session</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">Aucune formation ne correspond à votre recherche.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm text-gray-900"><?= h($row['labell']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['domaines']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['objectifs']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['cout']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['public']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['sessions_count']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($row['prochain_session']) ?></td>
                                <td class="px-4 py-4 text-right text-sm">
                                    <?php
                                        $isRegistered = isset($current['id_role']) && (int)$current['id_role'] === 1 && in_array((int)$row['id'], $alreadyRegisteredFormationIds, true);
                                    ?>
                                    <?php if (!empty($row['prochain_session_id'])): ?>
                                        <?php if ($isRegistered): ?>
                                            <span class="inline-flex items-center rounded-full bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700">Déjà inscrit</span>
                                        <?php elseif (isset($_SESSION['id_role']) && (int) $_SESSION['id_role'] === 2): ?>
                                            <a href="gestionaire/inscrire/?formation_id=<?= (int)$row['id'] ?>&amp;session_id=<?= (int)$row['prochain_session_id'] ?>" class="inline-flex items-center rounded-full bg-m2l-primary px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">S'inscrire</a>
                                        <?php else: ?>
                                            <a href="inscription/?session_id=<?= (int)$row['prochain_session_id'] ?>" class="inline-flex items-center rounded-full bg-m2l-primary px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">S'inscrire</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">Aucun créneau</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
