<?php
// includes/functions.php

// Treeningute pärimine (ainult need, mille lõpp on tulevikus või viimase 2 nädala sees)
function getUpcomingTrainings($conn, $today) {
    $twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-14 days'));
    $sql = "SELECT *, 
                TIMESTAMPADD(MINUTE, duration, CONCAT(date, ' ', time)) AS end_time 
            FROM trainings 
            WHERE TIMESTAMPADD(MINUTE, duration, CONCAT(date, ' ', time)) > :twoWeeksAgo
            ORDER BY date, time ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':twoWeeksAgo', $twoWeeksAgo);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Registreeringute arvu pärimine
function getRegisteredCount($conn, $trainingId) {
    $sql = "SELECT COUNT(*) FROM registrations 
            WHERE training_id = :training_id AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':training_id', $trainingId);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Kasutaja registreeringute kontroll
function isUserRegistered($conn, $userId, $trainingId) {
    $sql = "SELECT COUNT(*) FROM registrations 
            WHERE user_id = :user_id AND training_id = :training_id AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':training_id', $trainingId);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

// Kasutaja andmete pärimine
function getUserData($conn, $userId) {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Treeningu andmete pärimine
function getTrainingData($conn, $trainingId) {
    $sql = "SELECT * FROM trainings WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $trainingId);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Isikukoodi valideerimine
function validatePersonalId($personalId) {
    if(strlen($personalId) !== 11 || !is_numeric($personalId)) {
        return false;
    }
    
    $century = substr($personalId, 0, 1);
    if(!in_array($century, [3,4,5,6])) {
        return false;
    }
    
    return true;
}

// E-posti valideerimine
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Treeningu tühistamise kontroll (tänane kuupäev: 2025-06-11)
function canCancelTraining($trainingDate, $trainingTime) {
    $trainingDateTime = new DateTime("$trainingDate $trainingTime");
    $currentDateTime = new DateTime("2025-06-11 " . date('H:i:s'));
    $interval = $currentDateTime->diff($trainingDateTime);
    
    // Tühistamise aeg vähemalt 2 tundi enne treeningut
    return ($interval->days * 24 + $interval->h) >= 2;
}

// Kogu registreeringute arv
function getTotalRegistrations($conn, $today) {
    $sql = "SELECT COUNT(*) FROM registrations 
            WHERE status = 'active' AND training_id IN 
                (SELECT id FROM trainings WHERE date >= :today)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Kasutajate arv
function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Tänaste treeningute arv (tänane kuupäev: 2025-06-11)
function getTrainingsToday($conn, $today) {
    $sql = "SELECT COUNT(*) FROM trainings WHERE date = :today";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    return $stmt->fetchColumn();
}

// Kasutaja registreeringute pärimine
function getUserRegistrations($conn, $userId) {
    $sql = "SELECT r.*, t.title, t.type, t.date, t.time 
            FROM registrations r
            JOIN trainings t ON r.training_id = t.id
            WHERE r.user_id = :user_id
            ORDER BY t.date DESC, t.time DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Kasutaja on admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Kasutaja on sisse loginud
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>