<?php

declare(strict_types=1);

/**
 * Cấu hình chung — chỉnh BASE_URL theo môi trường local/production.
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL gốc (có slash cuối), ví dụ: http://localhost:8080/
const BASE_URL = 'http://localhost:8080/';
