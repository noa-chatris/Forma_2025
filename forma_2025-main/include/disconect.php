<?php
    function disconect(string $error = '') : void{
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        if ($error !== '') {
            header('Location: index.php?erreur=' . urlencode($error));
        } else {
            header('Location: index.php');
        }
        exit();
    }
?>