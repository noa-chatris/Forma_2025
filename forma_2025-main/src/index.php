<?php 
   header('Location: ' . "/", true, 200);
   echo("
        <script>
            window.location.replace('/');
        </script>
    ");
   exit();
?>