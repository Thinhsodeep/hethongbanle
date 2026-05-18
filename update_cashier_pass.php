<?php
require_once 'config/database.php';
require_once 'core/Database.php';
$db = Database::getInstance();
$hash = password_hash('Abc@12345', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = 'cashier.q1@retailchain.vn'");
$stmt->execute([$hash]);
echo "Updated cashier password.";
