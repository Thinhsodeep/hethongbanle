<?php

declare(strict_types=1);

class App
{
    public function __construct()
    {
        $url   = trim($_GET['url'] ?? 'auth/login', '/');
        $parts = $url !== '' ? explode('/', $url) : ['auth', 'login'];

        $controllerName = ucfirst($parts[0] ?? 'auth') . 'Controller';
        $method         = $parts[1] ?? 'index';
        $param          = $parts[2] ?? null;

        $file = APP_ROOT . '/app/Controllers/' . $controllerName . '.php';
        if (!is_file($file)) {
            $this->notFound();
        }

        require_once $file;

        if (!class_exists($controllerName)) {
            $this->notFound();
        }

        $ctrl = new $controllerName();
        if (!method_exists($ctrl, $method)) {
            $this->notFound();
        }

        $ctrl->$method($param);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }
}
