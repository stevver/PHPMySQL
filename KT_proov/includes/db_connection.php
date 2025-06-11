<?php
// includes/db_connection.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "spordisaal";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ühendus ebaõnnestus: " . $e->getMessage());
}
?>