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
$name = trim($data['device_name'] ?? '');
$ip = trim($data['ip_address'] ?? '');
$vendor = trim($data['vendor'] ?? '');
$model = trim($data['model'] ?? '');
$zone = trim($data['zone'] ?? '');
$sshUser = trim($data['ssh_username'] ?? '');
$sshPass = trim($data['ssh_password'] ?? '');
$sshPort = trim($data['ssh_port'] ?? '22');
if (!is_numeric($sshPort)) { $sshPort = 22; }

$allowedVendors = ['huawei', 'cisco', 'mikrotik', 'juniper', 'arista'];

if (empty($name) || empty($ip) || empty($vendor) || empty($zone) || empty($sshUser) || empty($sshPass)) {
    echo json_encode(["status" => "error", "message" => "Fill all required fields"]);
    exit;
}
if (!filter_var($ip, FILTER_VALIDATE_IP)) {
    echo json_encode(["status" => "error", "message" => "Enter a valid IP address"]);
    exit;
}
if (!in_array(strtolower($vendor), $allowedVendors)) {
    echo json_encode(["status" => "error", "message" => "Select a valid vendor"]);
    exit;
}

try {
    $check = $pdo->prepare("SELECT id FROM devices WHERE ip_address = :ip");
    $check->execute([':ip' => $ip]);
    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "A device with this IP address already exists"]);
        exit;
    }

    $encryptedPass = encryptPassword($sshPass);
    $stmt = $pdo->prepare("INSERT INTO devices (device_name, ip_address, vendor, model, zone, ssh_username, ssh_password, ssh_port, status) VALUES (:name, :ip, :vendor, :model, :zone, :ssh_user, :ssh_pass, :ssh_port, 'online')");
    $stmt->execute([
        ':name' => $name, ':ip' => $ip, ':vendor' => strtolower($vendor),
        ':model' => $model, ':zone' => $zone, ':ssh_user' => $sshUser, ':ssh_pass' => $encryptedPass, ':ssh_port' => $sshPort
    ]);

    echo json_encode(["status" => "success", "message" => "Device added successfully"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>