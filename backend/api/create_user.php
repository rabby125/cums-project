<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$deviceId = $data['device_id'] ?? '';
$targetUsername = trim($data['username'] ?? '');
$targetPassword = trim($data['password'] ?? '');

if (empty($deviceId) || empty($targetUsername) || empty($targetPassword)) {
    echo json_encode(["status" => "error", "message" => "Device, username and password required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $deviceId);
    $stmt->execute();
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        echo json_encode(["status" => "error", "message" => "Device not found"]);
        exit;
    }

    $scriptPath = __DIR__ . "/../python/ssh_engine.py";
    $realPassword = getUsablePassword($device['ssh_password']);

    $command = "python " . escapeshellarg($scriptPath) . " " .
               escapeshellarg("create") . " " .
               escapeshellarg($device['ip_address']) . " " .
               escapeshellarg($device['ssh_username']) . " " .
               escapeshellarg($realPassword) . " " .
               escapeshellarg($device['vendor']) . " " .
               escapeshellarg($targetUsername) . " " .
               escapeshellarg($targetPassword);

    $output = shell_exec($command . " 2>&1");
    $result = json_decode($output, true);

    if ($result === null) {
        echo json_encode(["status" => "error", "message" => "Python script error", "raw_output" => $output]);
        exit;
    }

    $logStmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, username_target, device_id, action, result, details) VALUES (:admin_id, :username_target, :device_id, 'create', :result, :details)");
    $logStmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':username_target' => $targetUsername,
        ':device_id' => $deviceId,
        ':result' => $result['status'],
        ':details' => $result['message'] ?? ''
    ]);

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>