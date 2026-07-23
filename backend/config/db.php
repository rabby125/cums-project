<?php
// Database connection settings
$host = "localhost";
$dbname = "cums_db";
$username = "root";
$password = "";  // XAMPP এ ডিফল্ট পাসওয়ার্ড খালি থাকে

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]));
}
?>