<?php

declare(strict_types=1);

final class App
{
    public function run(): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url((string) $uri, PHP_URL_PATH) ?: '/';

        // Router tối giản — mở rộng map route/controller sau
        if ($path === '/' || $path === '/index.php') {
            header('Content-Type: text/html; charset=utf-8');
            echo '<h1>Retail Chain System</h1><p>Router đã sẵn sàng — kết nối controller trong App.php.</p>';
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
