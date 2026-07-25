<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if (($_SESSION['role'] ?? '') !== 'super_admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized: Only super_admin can delete devices"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$deviceId = $data['device_id'] ?? '';

if (empty($deviceId)) {
    echo json_encode(["status" => "error", "message" => "Device ID required"]);
    exit;
}

try {
    // Soft delete — শুধু status বদলায়, নাম বদলায় না (বারবার delete করলেও নাম নষ্ট হবে না)
    $stmt = $pdo->prepare("UPDATE devices SET status = 'offline' WHERE id = :id");
    $stmt->execute([':id' => $deviceId]);
    echo json_encode(["status" => "success", "message" => "Device deactivated successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>