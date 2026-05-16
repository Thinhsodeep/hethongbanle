<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/Middleware.php';

final class AuthMiddleware extends Middleware
{
    public function handle(): bool
    {
        // Module 1 — kiểm tra session đăng nhập
        return isset($_SESSION['user_id']);
    }
}
