<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('ROOT_PATH', APP_ROOT);

define('BASE_URL', 'http://localhost:8080');

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
