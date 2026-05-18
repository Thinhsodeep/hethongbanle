<?php
require_once 'config/database.php';
require_once 'core/Database.php';
$db = Database::getInstance();
$hash = password_hash('Abc@12345', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = 'admin@retailchain.vn'");
$stmt->execute([$hash]);
echo "Password updated.";
