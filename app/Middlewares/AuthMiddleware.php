<?php

declare(strict_types=1);

class AuthMiddleware
{
    public static function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
}
