<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/crypto.php';

$stmt = $conn->query("SELECT id, ssh_password FROM devices");
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($devices as $device) {
    $encrypted = encryptPassword($device['ssh_password']);
    $update = $conn->prepare("UPDATE devices SET ssh_password = :pwd WHERE id = :id");
    $update->execute([':pwd' => $encrypted, ':id' => $device['id']]);
}

echo "সব পাসওয়ার্ড এনক্রিপ্ট করা হয়েছে। এই ফাইলটা এখনই ডিলিট করুন!";
?>