<?php
function error(): string
{
    return isset($_GET['erreur']) ? htmlspecialchars($_GET['erreur'], ENT_QUOTES, 'UTF-8') : '';
}
?>
