<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT al.id, al.username_target, al.action, al.result, al.details, al.created_at,
               a.username AS admin_username, d.device_name, d.ip_address
        FROM activity_logs al
        LEFT JOIN admins a ON al.admin_id = a.id
        LEFT JOIN devices d ON al.device_id = d.id
        ORDER BY al.created_at DESC
        LIMIT 10
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "logs" => $logs]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>