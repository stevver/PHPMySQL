<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Kontrolli, kas kasutaja on sisse logitud
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Kontrolli, kas registreeringu ID on olemas
if (!isset($_GET['registration_id'])) {
    header("Location: dashboard.php");
    exit;
}

$userId = $_SESSION['user_id'];
$registrationId = (int)$_GET['registration_id'];

// Kontrolli, kas registreering kuulub kasutajale ja on aktiivne
$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ? AND user_id = ? AND status = 'active'");
$stmt->execute([$registrationId, $userId]);
$registration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registration) {
    header("Location: dashboard.php?msg=not_found");
    exit;
}

// Tühista registreering
$stmt = $conn->prepare("UPDATE registrations SET status = 'cancelled' WHERE id = ?");
$stmt->execute([$registrationId]);

header("Location: dashboard.php?msg=cancelled");
exit;
?>