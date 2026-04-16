<?php
require_once __DIR__ . '/../../include/protection.php';
protect([2], strict: true);
require_once __DIR__ . '/../../include/bd.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

$domains = $pdo->query('SELECT id_domaine, labell FROM domaine ORDER BY labell ASC')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['labell'] ?? '');
    $objectif = trim($_POST['objectifs'] ?? '');
    $cout = trim($_POST['cout'] ?? '');
    $public = trim($_POST['public'] ?? '');
    $max = (int)($_POST['max_participant'] ?? 0);
    $domain_id = (int)($_POST['domain'] ?? 0);

    if ($action === 'add') {
        if ($nom === '' || $max <= 0 || $domain_id <= 0) {
            $error = 'Remplissez tous les champs obligatoires.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO formation (labell, max_participant, objectifs, cout, `public`) VALUES (:labell, :max, :objectifs, :cout, :public)');
            $stmt->execute([
                ':labell' => $nom,
                ':max' => $max,
                ':objectifs' => $objectif,
                ':cout' => $cout,
                ':public' => $public,
            ]);
            $formation_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO dom_forma (`id`, id_domaine) VALUES (:id, :domaine)');
            $stmt->execute([':id' => $formation_id, ':domaine' => $domain_id]);
            $message = 'Formation ajoutée avec succès.';
        }
    }

    if ($action === 'edit') {
        $formation_id = (int)($_GET['id'] ?? 0);
        if ($formation_id <= 0 || $nom === '' || $max <= 0 || $domain_id <= 0) {
            $error = 'Remplissez tous les champs obligatoires.';
        } else {
            $stmt = $pdo->prepare('UPDATE formation SET labell = :labell, max_participant = :max, objectifs = :objectifs, cout = :cout, `public` = :public WHERE id = :id');
            $stmt->execute([
                ':labell' => $nom,
                ':max' => $max,
                ':objectifs' => $objectif,
                ':cout' => $cout,
                ':public' => $public,
                ':id' => $formation_id,
            ]);
            $pdo->prepare('DELETE FROM dom_forma WHERE `id` = :id')->execute([':id' => $formation_id]);
            $pdo->prepare('INSERT INTO dom_forma (`id`, id_domaine) VALUES (:id, :domaine)')->execute([':id' => $formation_id, ':domaine' => $domain_id]);
            $message = 'Formation mise à jour.';
        }
    }

    if ($action === 'delete') {
        $formation_id = (int)($_GET['id'] ?? 0);
        if ($formation_id > 0) {
            $pdo->prepare('DELETE FROM dom_forma WHERE `id` = :id')->execute([':id' => $formation_id]);
            $pdo->prepare('DELETE FROM formation WHERE id = :id')->execute([':id' => $formation_id]);
            $message = 'Formation supprimée.';
        } else {
            $error = 'Identifiant de formation invalide.';
        }
    }
}

$selected = null;
if ($action === 'edit') {
    $formation_id = (int)($_GET['id'] ?? 0);
    if ($formation_id > 0) {
        $selected = $pdo->prepare('SELECT f.*, df.id_domaine FROM formation f LEFT JOIN dom_forma df ON f.id = df.id WHERE f.id = :id');
        $selected->execute([':id' => $formation_id]);
        $selected = $selected->fetch();
        if (!$selected) {
            $error = 'Formation introuvable.';
            $action = 'list';
        }
    } else {
        $error = 'Formation introuvable.';
        $action = 'list';
    }
}

$formations = $pdo->query('SELECT f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant, GROUP_CONCAT(d.labell SEPARATOR ", ") AS domaines FROM formation f LEFT JOIN dom_forma df ON f.id = df.id LEFT JOIN domaine d ON df.id_domaine = d.id_domaine GROUP BY f.id, f.labell, f.objectifs, f.cout, f.`public`, f.max_participant ORDER BY f.labell ASC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion du catalogue</h1>
                <p class="text-sm text-gray-500">Ajoutez, modifiez ou supprimez des formations.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../../catalogue/" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour Catalogue</a>
                <a href="../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-700"><?= h($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700"><?= h($error) ?></div>
        <?php endif; ?>

        <section class="mb-10 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4"><?= $action === 'edit' ? 'Modifier une formation' : 'Ajouter une formation' ?></h2>
            <form action="?action=<?= $action === 'edit' ? 'edit&id=' . (int)($_GET['id'] ?? 0) : 'add' ?>" method="POST" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm text-gray-700">
                        <span>Intitulé</span>
                        <input type="text" name="labell" value="<?= h($selected['labell'] ?? '') ?>" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </label>
                    <label class="block text-sm text-gray-700">
                        <span>Coût (€)</span>
                        <input type="number" step="0.01" name="cout" value="<?= h($selected['cout'] ?? '') ?>" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="block text-sm text-gray-700">
                        <span>Public</span>
                        <input type="text" name="public" value="<?= h($selected['public'] ?? '') ?>" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </label>
                    <label class="block text-sm text-gray-700">
                        <span>Places max</span>
                        <input type="number" name="max_participant" min="1" value="<?= h($selected['max_participant'] ?? '') ?>" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm text-gray-700">
                        <span>Domaine</span>
                        <select name="domain" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Sélectionner un domaine</option>
                            <?php foreach ($domains as $domain): ?>
                                <option value="<?= (int)$domain['id_domaine'] ?>" <?= isset($selected['id_domaine']) && $selected['id_domaine'] == $domain['id_domaine'] ? 'selected' : '' ?>><?= h($domain['labell']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block text-sm text-gray-700 sm:col-span-2">
                        <span>Objectifs</span>
                        <textarea name="objectifs" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"><?= h($selected['objectifs'] ?? '') ?></textarea>
                    </label>
                </div>
                <button type="submit" class="rounded-full bg-m2l-primary px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-600"><?= $action === 'edit' ? 'Enregistrer les modifications' : 'Ajouter la formation' ?></button>
                <?php if ($action === 'edit'): ?>
                    <a href="index.php" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Annuler</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Liste des formations</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Formation</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Domaine</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Coût</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Public</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Places</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <?php foreach ($formations as $formation): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm text-gray-900"><?= h($formation['labell']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($formation['domaines']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($formation['cout']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($formation['public']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?= h($formation['max_participant']) ?></td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <a href="?action=edit&id=<?= (int)$formation['id'] ?>" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                    <form action="?action=delete&id=<?= (int)$formation['id'] ?>" method="POST" class="inline-block ml-3" onsubmit="return confirm('Supprimer cette formation ?');">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
