<?php
// logout.php
session_start();

// Kustutame sessiooni andmed
$_SESSION = array();

// Kustutame sessiooni küpsise
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Kustutame "mäleta mind" küpsise
if(isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Hävitame sessiooni
session_destroy();

// Suuname kasutaja avalehele
header("Location: index.php");
exit;
?>