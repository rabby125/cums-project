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

if (empty($deviceId) || empty($targetUsername)) {
    echo json_encode(["status" => "error", "message" => "Device and username required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM devices WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $deviceId);
    $stmt->execute();
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        echo json_encode(["status" => "error", "message" => "Device not found"]);
        exit;
    }

    $pythonPath = "python";
    $scriptPath = __DIR__ . "/../python/ssh_engine.py";

    $command = escapeshellcmd($pythonPath) . " " . escapeshellarg($scriptPath) . " " .
               escapeshellarg("delete") . " " .
               escapeshellarg($device['ip_address']) . " " .
               escapeshellarg($device['ssh_username']) . " " .
               escapeshellarg(decryptPassword($device['ssh_password'])) . " " .
               escapeshellarg($device['vendor']) . " " .
               escapeshellarg($targetUsername);

    $output = shell_exec($command . " 2>&1");
    $result = json_decode($output, true);

    if ($result === null) {
        echo json_encode(["status" => "error", "message" => "Python script error", "raw_output" => $output]);
        exit;
    }

    $logStmt = $conn->prepare("INSERT INTO activity_logs (admin_id, username_target, device_id, action, result, details) 
                                VALUES (:admin_id, :username_target, :device_id, 'delete', :result, :details)");
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