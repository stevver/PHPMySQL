<?php
    // Alustame sessiooni
    session_start();

    // Kontrollime, kas kasutaja on sisse loginud
    if (!isset($_SESSION['tuvastamine'])) {
        // Kui mitte, suuname sisselogimise lehele
        header('Location: login.php');
        exit();
    }

    // Kui URL-is on logout parameeter, logime välja
    if (isset($_GET['logout'])) {
        // Sessiooni lõpetamine
        session_destroy();
        // Suuname tagasi login lehele
        header('Location: login.php');
        exit();
    }
?>