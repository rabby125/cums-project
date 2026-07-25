<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, device_name, ip_address, vendor, model, zone, ssh_username, status FROM devices ORDER BY id DESC");
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "devices" => $devices]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>