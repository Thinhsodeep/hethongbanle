<?php

declare(strict_types=1);

/**
 * Test nhanh Groq API key (CLI: php test_groq.php hoặc mở trên browser local).
 */
require_once __DIR__ . '/config/config.php';
require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/app/Services/GroqClient.php';

header('Content-Type: text/plain; charset=utf-8');

if (!GROQ_API_KEY) {
    echo "GROQ_API_KEY trống.\n";
    echo "Điền trong .env hoặc config/constants.php\n";
    exit(1);
}

try {
    $client = new GroqClient();
    $r = $client->chatCompletion([
        ['role' => 'user', 'content' => 'Trả JSON: {"ok":true,"message":"groq works"}'],
    ], true);
    echo "OK\n";
    echo $r['content'] . "\n";
} catch (Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
