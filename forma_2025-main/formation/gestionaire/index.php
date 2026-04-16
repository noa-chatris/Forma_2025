<?php
require_once __DIR__ . '/../../include/protection.php';
protect([2], strict: true);
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once __DIR__ . '/../../include/header.php' ?>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Espace gestionnaire</h1>
                <p class="text-sm text-gray-500">Accédez aux outils de gestion des inscriptions et aux listes.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="../../" class="inline-flex items-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour Formation</a>
                <a href="../../../deconnexion.php" class="inline-flex items-center rounded-full border border-red-500 bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Se déconnecter</a>
            </div>
        </header>

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="reglement/" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-slate-300">
                <h2 class="font-semibold text-lg text-gray-900">Enregistrer règlement</h2>
                <p class="text-sm text-gray-500 mt-2">Validez les inscriptions après réception du paiement.</p>
            </a>
            <a href="liste/" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-slate-300">
                <h2 class="font-semibold text-lg text-gray-900">Liste des participants</h2>
                <p class="text-sm text-gray-500 mt-2">Affichez les participants une semaine avant chaque session.</p>
            </a>
            <a href="historiser/" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-slate-300">
                <h2 class="font-semibold text-lg text-gray-900">Historiser</h2>
                <p class="text-sm text-gray-500 mt-2">Consultez l’historique des participations et exportez les données.</p>
            </a>
            <a href="inscrire/" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-slate-300">
                <h2 class="font-semibold text-lg text-gray-900">Inscrire un utilisateur</h2>
                <p class="text-sm text-gray-500 mt-2">Inscrivez un utilisateur à une session future directement depuis le gestionnaire.</p>
            </a>
            <a href="utilisateurs/" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:border-slate-300">
                <h2 class="font-semibold text-lg text-gray-900">Gestion des utilisateurs</h2>
                <p class="text-sm text-gray-500 mt-2">Créez manuellement des comptes pour vos collaborateurs et partenaires.</p>
            </a>
        </div>
    </main>
</body>
</html>
