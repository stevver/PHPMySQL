<?php
require_once 'db_connection.php';
require_once 'functions.php';

// Kasutaja sisselogimine
function login($email, $password, $remember = false) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];

        if ($remember) {
            // "Mäleta mind" küpsis 4 tunniks
            setcookie('remember_me', $user['id'], time() + (4 * 3600), '/');
        }

        return true;
    }

    return false;
}

// Kasutaja väljalogimine
function logout() {
    session_unset();
    session_destroy();
    setcookie('remember_me', '', time() - 3600, '/');
}

// Kasutaja registreerimine
function register_user($first_name, $last_name, $personal_id, $email, $password) {
    global $conn;

    if (!validatePersonalId($personal_id)) {
        return "Vigane isikukood";
    }

    if (!validateEmail($email)) {
        return "Vigane e-posti aadress";
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, personal_id, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $personal_id, $email, $hashed_password]);
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return "E-posti aadress või isikukood on juba kasutusel";
        }
        return "Registreerimise viga: " . $e->getMessage();
    }
}

// Kasutaja rolli kontroll
function check_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: index.php");
        exit();
    }
}
?>