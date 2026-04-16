<?php
require_once __DIR__ . '/../../../include/protection.php';
protect([2], strict: true);
require_once __DIR__ . '/../../../include/bd.php';

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$message = '';
$errors = [];
$values = [
    'nom' => '',
    'prenom' => '',
    'adresse' => '',
    'code_postal' => '',
    'ville' => '',
    'email' => '',
    'fonction' => '',
    'icom' => '',
    'id_role' => '',
    'id_statut' => '',
    'login' => '',
    'password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    if ($values['nom'] === '') {
        $errors[] = 'Le nom est requis.';
    }
    if ($values['prenom'] === '') {
        $errors[] = 'Le prénom est requis.';
    }
    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Une adresse e-mail valide est requise.';
    }
    if ($values['login'] === '') {
        $errors[] = 'Le login est requis.';
    }
    if ($values['password'] === '') {
        $errors[] = 'Le mot de passe est requis.';
    }
    if ($values['id_role'] === '') {
        $errors[] = 'Le rôle est requis.';
    }
    if ($values['id_statut'] === '') {
        $errors[] = 'Le statut est requis.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, adresse, code_postal, ville, email, fonction, icom, id_role, id_statut, login, password) VALUES (:nom, :prenom, :adresse, :code_postal, :ville, :email, :fonction, :icom, :id_role, :id_statut, :login, :password)'
        );

        $stmt->execute([
            ':nom' => $values['nom'],
            ':prenom' => $values['prenom'],
            ':adresse' => $values['adresse'],
            ':code_postal' => $values['code_postal'],
            ':ville' => $values['ville'],
            ':email' => $values['email'],
            ':fonction' => $values['fonction'],
            ':icom' => $values['icom'] !== '' ? $values['icom'] : null,
            ':id_role' => (int) $values['id_role'],
            ':id_statut' => (int) $values['id_statut'],
            ':login' => $values['login'],
            ':password' => $values['password'],
        ]);

        $message = 'Utilisateur créé avec succès.';
        foreach ($values as $key => $_) {
            $values[$key] = '';
        }
    }
}

$associations = $pdo->query('SELECT icom, nom FROM association ORDER BY nom ASC')->fetchAll();
$roles = $pdo->query('SELECT id, libelle FROM role ORDER BY id ASC')->fetchAll();
$statuts = $pdo->query('SELECT id, libelle FROM statut ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gestion des utilisateurs</h1>
                <p class="text-sm text-gray-500">Créez et gérez les comptes depuis le module gestionnaire.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour espace gestionnaire</a>
                <a href="../../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 p-4 text-sm text-green-700"><?= h($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="grid gap-6 bg-white border border-gray-200 rounded-3xl p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Nom</span>
                    <input name="nom" value="<?= h($values['nom']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Prénom</span>
                    <input name="prenom" value="<?= h($values['prenom']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Adresse</span>
                    <input name="adresse" value="<?= h($values['adresse']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Code postal</span>
                    <input name="code_postal" value="<?= h($values['code_postal']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Ville</span>
                    <input name="ville" value="<?= h($values['ville']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">E-mail</span>
                    <input name="email" type="email" value="<?= h($values['email']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Fonction</span>
                    <input name="fonction" value="<?= h($values['fonction']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Association (ICOM)</span>
                    <select name="icom" class="mt-2 block w-full rounded-xl border-gray-200 bg-white p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                        <option value="">Aucune</option>
                        <?php foreach ($associations as $association): ?>
                            <option value="<?= h($association['icom']) ?>"<?= $values['icom'] === $association['icom'] ? ' selected' : '' ?>><?= h($association['nom']) ?> (<?= h($association['icom']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Rôle</span>
                    <select name="id_role" class="mt-2 block w-full rounded-xl border-gray-200 bg-white p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                        <option value="">Choisir un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= h($role['id']) ?>"<?= $values['id_role'] === (string) $role['id'] ? ' selected' : '' ?>><?= h($role['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Statut</span>
                    <select name="id_statut" class="mt-2 block w-full rounded-xl border-gray-200 bg-white p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                        <option value="">Choisir un statut</option>
                        <?php foreach ($statuts as $statut): ?>
                            <option value="<?= h($statut['id']) ?>"<?= $values['id_statut'] === (string) $statut['id'] ? ' selected' : '' ?>><?= h($statut['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Login</span>
                    <input name="login" value="<?= h($values['login']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Mot de passe</span>
                    <input name="password" type="password" value="<?= h($values['password']) ?>" class="mt-2 block w-full rounded-xl border-gray-200 bg-gray-50 p-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                </label>
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Créer l'utilisateur</button>
        </form>
    </main>
</body>
</html>
