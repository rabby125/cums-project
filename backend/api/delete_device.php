<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// শুধু super_admin ডিভাইস ডিলিট করতে পারবে
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
    // Soft delete — activity_logs এর সাথে foreign key সমস্যা এড়াতে সরাসরি না মুছে status বদলাই
    $stmt = $pdo->prepare("UPDATE devices SET status = 'offline', device_name = CONCAT(device_name, ' (Deleted)') WHERE id = :id");
    $stmt->execute([':id' => $deviceId]);
    echo json_encode(["status" => "success", "message" => "Device deleted successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

    $command = "python " . escapeshellarg($scriptPath) . " " .
           escapeshellarg("verify") . " " .
           escapeshellarg($device['ip_address']) . " " .
           escapeshellarg($device['ssh_username']) . " " .
           escapeshellarg($realPassword) . " " .
           escapeshellarg($device['vendor']) . " " .
           escapeshellarg($targetUsername) . " " .
           escapeshellarg("--port=" . ($device['ssh_port'] ?? 22));
           " . escapeshellarg("--port=" . ($device['ssh_port'] ?? 22))
?>