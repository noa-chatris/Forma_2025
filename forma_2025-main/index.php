<?php
require './include/catch_get_error.php';
session_start();

function redirect(): void
{
    if (isset($_SESSION['id_role']) && $_SESSION['id_role'] !== 1) {
        header("Location: catalogue/");
        exit();
    }

    header("Location: formation/");
    exit();
}

if (isset($_SESSION['id_role'])) {
    redirect();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once './include/bd.php';
    $prep = $pdo->prepare("SELECT * FROM utilisateur WHERE login = :login AND password = :password;");
    $prep->execute([
        ':login' => trim($_POST['login'] ?? ''),
        ':password' => trim($_POST['password'] ?? ''),
    ]);
    $result = $prep->fetchAll();

    if (count($result)) {
        $result = $result[0];
        $_SESSION['id'] = $result['id'];
        $_SESSION['nom'] = $result['nom'];
        $_SESSION['prenom'] = $result['prenom'];
        $_SESSION['fonction'] = $result['fonction'];
        $_SESSION['id_role'] = $result['id_role'];
        $_SESSION['id_statut'] = $result['id_statut'];
        redirect();
    }

    header('Location: /?erreur=' . urlencode('Identifiant ou mot de passe incorrect'));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php require_once "./include/header.php" ?>
<body class="bg-gray-50 min-h-screen flex items-center justify-center font-sans text-gray-800">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden m-4">
        <div class="bg-gray-100 p-8 flex flex-col items-center border-b border-gray-200">
            <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 border-4 border-m2l-primary">
               <img src="./src/MDL.png" alt="Logo M2L" class="w-28 h-28 object-contain rounded-full">
            </div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-wide">MAISON DES LIGUES</h2>
            <p class="text-sm text-gray-500 mt-1">Portail d'identification</p>
        </div>

        <div class="p-8">
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-2">Identifiant</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="login" name="login" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-m2l-primary focus:border-m2l-primary transition duration-150 ease-in-out sm:text-sm placeholder-gray-400"
                            placeholder="Entrez votre identifiant">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-m2l-primary focus:border-m2l-primary transition duration-150 ease-in-out sm:text-sm placeholder-gray-400"
                            placeholder="••••••••">
                    </div>
                </div>

                <?php if (error()): ?>
                    <p class="erreur"><?= error() ?></p>
                <?php endif; ?>

                <button type="submit"
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-m2l-primary hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out uppercase tracking-wider transform hover:-translate-y-0.5">
                    Se connecter
                </button>
            </form>
        </div>

        <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 text-center">
            <p class="text-xs text-gray-500">&copy; 2024 Maison des Ligues. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
