<?php

declare(strict_types=1);

require_once APP_ROOT . '/config/constants.php';

final class GroqClient
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey  = GROQ_API_KEY;
        $this->model   = GROQ_MODEL;
        $this->timeout = GROQ_TIMEOUT;
    }

    public function isConfigured(): bool
    {
        return trim($this->apiKey) !== '';
    }

    /**
     * @param list<array{role: string, content: string}> $messages
     * @return array{content: string, raw: array}
     */
    public function chatCompletion(array $messages, bool $jsonMode = true): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Chưa cấu hình GROQ_API_KEY');
        }

        $body = [
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.1,
        ];
        if ($jsonMode) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        if ($ch === false) {
            throw new RuntimeException('Không khởi tạo được curl');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Lỗi kết nối Groq: ' . $curlErr);
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Phản hồi Groq không hợp lệ');
        }

        if ($httpCode >= 400) {
            $msg = $data['error']['message'] ?? "HTTP $httpCode";
            throw new RuntimeException('Groq API: ' . $msg);
        }

        $content = (string) ($data['choices'][0]['message']['content'] ?? '');

        return ['content' => trim($content), 'raw' => $data];
    }
}
