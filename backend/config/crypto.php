<?php
define('ENCRYPTION_KEY', 'cums_secret_key_2026_change_this'); // ২৪+ ক্যারেক্টার হওয়া ভালো
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encryptPassword($plainText) {
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plainText, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptPassword($encryptedText) {
    $data = base64_decode($encryptedText);
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    return openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
}
?>