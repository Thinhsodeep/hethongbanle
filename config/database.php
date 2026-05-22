<?php

declare(strict_types=1);

$dbConfig = [
    'host' => '127.0.0.1',
    'name' => 'retail_chain',
    'user' => 'root',
    'pass' => '',
];

if (is_file(__DIR__ . '/database.local.php')) {
    /** @var array{host?:string,name?:string,user?:string,pass?:string} $local */
    $local = require __DIR__ . '/database.local.php';
    $dbConfig = array_merge($dbConfig, $local);
}

define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['name']);
define('DB_USER', $dbConfig['user']);
define('DB_PASS', $dbConfig['pass']);
