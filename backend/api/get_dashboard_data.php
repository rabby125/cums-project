<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $totalDevices = $pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
    $onlineDevices = $pdo->query("SELECT COUNT(*) FROM devices WHERE status='online'")->fetchColumn();
    $todayOps = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $totalVendors = $pdo->query("SELECT COUNT(DISTINCT vendor) FROM devices")->fetchColumn();

    echo json_encode([
        "status" => "success",
        "total_devices" => (int)$totalDevices,
        "online_devices" => (int)$onlineDevices,
        "total_users" => 0,
        "total_ops" => (int)$todayOps,
        "total_vendors" => (int)$totalVendors
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>