<?php
require_once 'config/config.php';
$url = 'pos/search';
$parts = explode('/', $url);
$controllerName = ucfirst($parts[0] ?? 'auth') . 'Controller';
$method         = $parts[1] ?? 'index';
$file = APP_ROOT . '/app/Controllers/' . $controllerName . '.php';

echo "File: $file\n";
echo "is_file: " . (is_file($file) ? 'YES' : 'NO') . "\n";
require_once $file;
echo "class_exists: " . (class_exists($controllerName) ? 'YES' : 'NO') . "\n";
$ctrl = new $controllerName();
echo "method_exists: " . (method_exists($ctrl, $method) ? 'YES' : 'NO') . "\n";
