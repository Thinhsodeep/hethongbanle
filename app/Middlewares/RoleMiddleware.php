<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/AuthMiddleware.php';

class RoleMiddleware
{
    public static function require(string ...$roles): void
    {
        AuthMiddleware::handle();
        if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
            http_response_code(403);
            require_once APP_ROOT . '/app/Views/errors/403.php';
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function isManager(): bool
    {
        return ($_SESSION['role'] ?? '') === 'manager';
    }

    public static function isStaff(): bool
    {
        return ($_SESSION['role'] ?? '') === 'staff';
    }

    public static function isCashier(): bool
    {
        return ($_SESSION['role'] ?? '') === 'cashier';
    }
}
