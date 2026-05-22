<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Services/EnvLoader.php';

EnvLoader::load(dirname(__DIR__) . '/.env');

/**
 * Groq (Bearer) — để trống thì import Excel dùng map heuristic (regex tên cột).
 * Có thể điền key tại đây HOẶC trong file .env (GROQ_API_KEY).
 * Lấy key: https://console.groq.com/keys
 */
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: '');
    // Hoặc dán key trực tiếp (không commit nếu repo public):
    // define('GROQ_API_KEY', 'gsk_...');
}

if (!defined('GROQ_MODEL')) {
    define('GROQ_MODEL', $_ENV['GROQ_MODEL'] ?? getenv('GROQ_MODEL') ?: 'llama-3.1-8b-instant');
}

if (!defined('GROQ_TIMEOUT')) {
    define('GROQ_TIMEOUT', (int) ($_ENV['GROQ_TIMEOUT'] ?? getenv('GROQ_TIMEOUT') ?: 30));
}

if (!defined('IMPORT_MAX_ROWS')) {
    define('IMPORT_MAX_ROWS', (int) ($_ENV['IMPORT_MAX_ROWS'] ?? getenv('IMPORT_MAX_ROWS') ?: 500));
}

/** Ghi debug_import_*.txt ở thư mục gốc project khi true */
if (!defined('IMPORT_DEBUG')) {
    define('IMPORT_DEBUG', filter_var(
        $_ENV['IMPORT_DEBUG'] ?? getenv('IMPORT_DEBUG') ?: '0',
        FILTER_VALIDATE_BOOLEAN
    ));
}

/** Cấu hình ngân hàng cho thanh toán VietQR */
if (!defined('BANK_ID')) {
    define('BANK_ID', $_ENV['BANK_ID'] ?? getenv('BANK_ID') ?: 'MB');
}

if (!defined('BANK_ACCOUNT_NO')) {
    define('BANK_ACCOUNT_NO', $_ENV['BANK_ACCOUNT_NO'] ?? getenv('BANK_ACCOUNT_NO') ?: '0901111001');
}

if (!defined('BANK_ACCOUNT_NAME')) {
    define('BANK_ACCOUNT_NAME', $_ENV['BANK_ACCOUNT_NAME'] ?? getenv('BANK_ACCOUNT_NAME') ?: 'NGUYEN VAN ADMIN');
}

if (!defined('PAYOS_CLIENT_ID')) {
    define('PAYOS_CLIENT_ID', $_ENV['PAYOS_CLIENT_ID'] ?? getenv('PAYOS_CLIENT_ID') ?: '');
}

if (!defined('PAYOS_API_KEY')) {
    define('PAYOS_API_KEY', $_ENV['PAYOS_API_KEY'] ?? getenv('PAYOS_API_KEY') ?: '');
}

if (!defined('PAYOS_CHECKSUM_KEY')) {
    define('PAYOS_CHECKSUM_KEY', $_ENV['PAYOS_CHECKSUM_KEY'] ?? getenv('PAYOS_CHECKSUM_KEY') ?: '');
}


