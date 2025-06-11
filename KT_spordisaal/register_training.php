<?php
session_start();
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Kontrolli, kas kasutaja on sisse logitud
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Kontrolli, kas treeningu ID on olemas
if (!isset($_GET['training_id'])) {
    header("Location: trainings.php");
    exit;
}

$userId = $_SESSION['user_id'];
$trainingId = (int)$_GET['training_id'];

// Kontrolli, kas kasutaja on juba registreerunud
$stmt = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND training_id = ? AND status = 'active'");
$stmt->execute([$userId, $trainingId]);
if ($stmt->fetch()) {
    header("Location: trainings.php?training_id=$trainingId&msg=already_registered");
    exit;
}

// Kontrolli, kas treeningul on vabu kohti
$stmt = $conn->prepare("SELECT max_participants FROM trainings WHERE id = ?");
$stmt->execute([$trainingId]);
$training = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$training) {
    header("Location: trainings.php?msg=training_not_found");
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE training_id = ? AND status = 'active'");
$stmt->execute([$trainingId]);
$registeredCount = $stmt->fetchColumn();

if ($registeredCount >= $training['max_participants']) {
    header("Location: trainings.php?training_id=$trainingId&msg=full");
    exit;
}

// Registreeri kasutaja treeningule
$stmt = $conn->prepare("INSERT INTO registrations (user_id, training_id, status, registered_at) VALUES (?, ?, 'active', NOW())");
$stmt->execute([$userId, $trainingId]);

header("Location: trainings.php?training_id=$trainingId&msg=registered");
exit;
?>