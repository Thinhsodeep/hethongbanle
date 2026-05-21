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
