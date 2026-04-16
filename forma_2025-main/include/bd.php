<?php
    try {
        $host="localhost";
        $dbname = "FORMA";
        $user = 'app';
        $pass = 'Azerty31';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        // DÃ©finir le mode d'erreur PDO sur Exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'database error, contact the administrator';
        die;
    }
?>