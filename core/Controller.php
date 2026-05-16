<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = ROOT_PATH . '/app/Views/' . $path . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($path);
            return;
        }
        require $file;
    }
}
