<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function protect(array $group_authorized = [1, 2, 3], string $path_if_invalide = '/', bool $strict = true): void
{
    if (!isset($_SESSION['id_role'])) {
        header('Location: /?erreur=' . urlencode('non connecté'));
        exit();
    }

    $current_role = (int) $_SESSION['id_role'];
    if (in_array($current_role, $group_authorized, true)) {
        return;
    }

    if (!$strict) {
        $group_list = [1, 2, 3];
        $pos = array_search($current_role, $group_list, true);
        foreach ($group_authorized as $group) {
            $group_pos = array_search($group, $group_list, true);
            if ($pos !== false && $group_pos !== false && $pos >= $group_pos) {
                return;
            }
        }
    }

    header('Location: ' . trim($path_if_invalide) . '/?erreur=' . urlencode('accès non autorisé'));
    exit();
}

function current_user(): array
{
    return $_SESSION ?? [];
}

function user_has_status(array $statuses): bool
{
    return isset($_SESSION['id_statut']) && in_array((int) $_SESSION['id_statut'], $statuses, true);
}
?>
