<?php
define('ENCRYPTION_KEY', 'cums_secret_key_2026_change_this_32ch');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encryptPassword($plainText) {
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($plainText, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// এই ফাংশন encrypted অথবা plain টেক্সট দুটোই হ্যান্ডেল করে —
// যদি decrypt ব্যর্থ হয় (মানে ডেটা আসলে plain text ছিল), তাহলে original ফিরিয়ে দেয়
function getUsablePassword($storedValue) {
    $ivLength = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $data = base64_decode($storedValue, true);
    if ($data === false || strlen($data) <= $ivLength) {
        return $storedValue; // plain text ছিল
    }
    $iv = substr($data, 0, $ivLength);
    $encrypted = substr($data, $ivLength);
    $decrypted = openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return ($decrypted === false) ? $storedValue : $decrypted;
}
?>