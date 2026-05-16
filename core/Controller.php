<?php

declare(strict_types=1);

class Controller
{
    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require_once APP_ROOT . '/app/Views/layouts/header.php';
        require_once APP_ROOT . '/app/Views/' . $path . '.php';
        require_once APP_ROOT . '/app/Views/layouts/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
