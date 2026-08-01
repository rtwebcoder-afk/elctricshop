<?php
// Database Credentials
$host     = 'localhost;port=3307';    // Apne Navicat/MySQL ka host rakhein
$username = 'root';        // Apne Navicat/MySQL ka username rakhein
$password = 'admin123';            // Apne Navicat/MySQL ka password rakhein
$dbname   = 'fs_solar'; // Apne Database ka naam rakhein

try {
    // PDO Connection (Secure & Recommended)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}