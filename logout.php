<?php
    session_start();
    if (!isset($_SESSION['tuvastamine'])) {
        header('Location: login.php');
        exit();
    }
    if(isset($_GET['logout'])){
        session_destroy();
        header('Location: login.php');
        exit();
    }
?>