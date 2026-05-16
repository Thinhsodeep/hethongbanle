<?php

declare(strict_types=1);

/**
 * Chạy: php database/patch_passwords.php
 * Đặt mật khẩu Abc@12345 cho tài khoản seed.
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/core/Database.php';

$hash = '$2y$10$qqcSFUv5EgyrQQe9jB.qDesGSfElrtFaDlj2JxyMvK4UJAlIL/HmS';
$db   = Database::getInstance();
$stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE email LIKE ?');
$emails = [
    'admin@retailchain.vn',
    'manager.q1@retailchain.vn',
    'manager.q7@retailchain.vn',
    'manager.bt@retailchain.vn',
    'staff.q1@retailchain.vn',
    'cashier.q1@retailchain.vn',
];
foreach ($emails as $email) {
    $stmt->execute([$hash, $email]);
    echo "Updated: $email\n";
}
echo "Done.\n";
