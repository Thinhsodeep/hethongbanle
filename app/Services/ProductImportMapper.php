<?php

declare(strict_types=1);

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/app/Services/GroqClient.php';

/**
 * Map cột Excel → trường hệ thống.
 * Tầng 1: dữ liệu đã trích từ file (SheetJS phía client gửi headers + sample rows).
 * Tầng 2: Groq AI hoặc heuristic (regex tên cột) nếu không có key / API lỗi.
 */
final class ProductImportMapper
{
    /** @var list<string> */
    public const SYSTEM_FIELDS = [
        'name',
        'sku',
        'category_name',
        'sell_price',
        'import_price',
        'barcode',
        'unit',
        'description',
        'color',
        'size',
        'attribute',
        'quantity',
        '_skip',
    ];

    public bool $lastMapUsedAi = false;

    private GroqClient $groq;

    public function __construct()
    {
        $this->groq = new GroqClient();
    }

    /**
     * @param list<string> $headers
     * @param list<array<string, string>> $sampleRows
     * @return array{column_map: array<string, string>, defaults: array<string, string>, confidence: string, notes: string}
     */
    public function mapColumns(array $headers, array $sampleRows): array
    {
        $this->lastMapUsedAi = false;

        if ($this->groq->isConfigured()) {
            try {
                $result = $this->mapWithAI($headers, $sampleRows);
                $this->lastMapUsedAi = true;
                $this->debugWrite('debug_import_ai_result.txt', json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                return $result;
            } catch (Throwable $e) {
                $this->debugWrite('debug_import_groq_error.txt', $e->getMessage());
            }
        }

        return $this->mapWithHeuristic($headers);
    }

    /**
     * @param list<string> $headers
     * @param list<array<string, string>> $sampleRows
     * @return array{column_map: array<string, string>, defaults: array<string, string>, confidence: string, notes: string}
     */
    public function mapWithAI(array $headers, array $sampleRows): array
    {
        $prompt = $this->buildPrompt($headers, $sampleRows);

        $result = $this->groq->chatCompletion([
            ['role' => 'system', 'content' => 'Bạn map cột Excel sang JSON. Chỉ trả JSON hợp lệ.'],
            ['role' => 'user', 'content' => $prompt],
        ], true);

        $this->debugWrite('debug_import_groq_raw.txt', $result['content']);

        $parsed = $this->parseJsonResponse($result['content']);
        $normalized = $this->normalizeMapping($parsed, $headers);
        $normalized['notes'] = trim(($normalized['notes'] ?? '') . ' (Groq AI)');
        return $normalized;
    }

    /**
     * Fallback: đoán trường theo regex tên cột (giống CV parse regex).
     *
     * @param list<string> $headers
     * @return array{column_map: array<string, string>, defaults: array<string, string>, confidence: string, notes: string}
     */
    public function mapWithHeuristic(array $headers): array
    {
        $columnMap = [];
        foreach ($headers as $header) {
            $columnMap[$header] = $this->guessFieldFromHeader($header);
        }

        return [
            'column_map' => $columnMap,
            'defaults'   => ['unit' => 'cái'],
            'confidence' => 'low',
            'notes'      => $this->groq->isConfigured()
                ? 'Groq lỗi hoặc JSON không hợp lệ — dùng map tự động theo tên cột.'
                : 'Chưa có GROQ_API_KEY — dùng map tự động theo tên cột (regex).',
        ];
    }

    private function guessFieldFromHeader(string $header): string
    {
        $h = mb_strtolower(trim($header), 'UTF-8');

        if (preg_match('/tên|ten|name|sản phẩm|san pham|hàng hóa|hang hoa/u', $h)) {
            return 'name';
        }
        if (preg_match('/sku|mã hàng|ma hang|ma sp|mã sp/u', $h)) {
            return 'sku';
        }
        if (preg_match('/\b(mã|ma)\b/u', $h) && !preg_match('/vạch|vach|barcode/u', $h)) {
            return 'sku';
        }
        if (preg_match('/vạch|vach|barcode|ean/u', $h)) {
            return 'barcode';
        }
        if (preg_match('/nhóm|nhom|loại|loai|danh mục|danh muc|category/u', $h)) {
            return 'category_name';
        }
        if (preg_match('/giá bán|gia ban|sell/u', $h) && !preg_match('/nhập|nhap|vốn|von/u', $h)) {
            return 'sell_price';
        }
        if (preg_match('/giá nhập|gia nhap|vốn|von|cost/u', $h) && !preg_match('/đơn nhập hàng|don nhap hang/u', $h)) {
            return 'import_price';
        }
        if (preg_match('/\bprice\b/u', $h) && !preg_match('/nhập|nhap|vốn|von/u', $h)) {
            return 'sell_price';
        }
        if (preg_match('/tồn|ton|sl\b|số lượng|so luong|qty|quantity|kho/u', $h)) {
            return 'quantity';
        }
        if (preg_match('/đơn vị|don vi|unit/u', $h)) {
            return 'unit';
        }
        if (preg_match('/màu|mau|color/u', $h)) {
            return 'color';
        }
        if (preg_match('/\bsize\b|cỡ|co\b/u', $h)) {
            return 'size';
        }

        return '_skip';
    }

    /**
     * @param list<string> $headers
     * @param list<array<string, string>> $sampleRows
     */
    private function buildPrompt(array $headers, array $sampleRows): string
    {
        $fieldsDesc = <<<'TXT'
Các trường hệ thống (chỉ map vào các key này):
- name (bắt buộc): tên sản phẩm
- sku (bắt buộc): mã SKU / mã hàng, duy nhất
- category_name: tên danh mục / nhóm hàng
- sell_price: giá bán
- import_price: giá nhập / giá vốn
- barcode: mã vạch
- unit: đơn vị tính (cái, hộp, kg...)
- description: mô tả
- color, size, attribute: thuộc tính biến thể
- quantity: tồn kho / số lượng
- _skip: cột không dùng
TXT;

        $payload = json_encode([
            'excel_headers' => $headers,
            'sample_rows'   => array_slice($sampleRows, 0, 10),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Bạn là trợ lý map cột Excel sang hệ thống quản lý bán lẻ Việt Nam.

{$fieldsDesc}

Dữ liệu Excel (headers + vài dòng mẫu):
{$payload}

Trả về JSON thuần, đúng schema:
{
  "column_map": { "Tên cột Excel": "system_field", ... },
  "defaults": { "unit": "cái" },
  "confidence": "high|medium|low",
  "notes": "ghi chú ngắn tiếng Việt"
}

Quy tắc:
- Mỗi cột Excel chỉ map tối đa 1 trường hệ thống.
- Bắt buộc map được name và sku nếu có cột tương ứng.
- Cột không nhận diện được → "_skip".
- Tên cột trong column_map phải khớp chính xác excel_headers.
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJsonResponse(string $text): array
    {
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```\s*$/', '', $text) ?? $text;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new RuntimeException('Groq trả về JSON không hợp lệ');
    }

    /**
     * @param array<string, mixed> $parsed
     * @param list<string> $headers
     * @return array{column_map: array<string, string>, defaults: array<string, string>, confidence: string, notes: string}
     */
    private function normalizeMapping(array $parsed, array $headers): array
    {
        $rawMap = is_array($parsed['column_map'] ?? null) ? $parsed['column_map'] : [];
        $allowed = array_flip(self::SYSTEM_FIELDS);
        $columnMap = [];

        foreach ($headers as $header) {
            $target = $rawMap[$header] ?? '_skip';
            if (!is_string($target) || !isset($allowed[$target])) {
                $target = '_skip';
            }
            $columnMap[$header] = $target;
        }

        $defaults = [];
        if (is_array($parsed['defaults'] ?? null)) {
            foreach ($parsed['defaults'] as $k => $v) {
                if (is_string($k) && is_string($v)) {
                    $defaults[$k] = $v;
                }
            }
        }

        return [
            'column_map' => $columnMap,
            'defaults'   => $defaults,
            'confidence' => is_string($parsed['confidence'] ?? null) ? $parsed['confidence'] : 'medium',
            'notes'      => is_string($parsed['notes'] ?? null) ? $parsed['notes'] : '',
        ];
    }

    private function debugWrite(string $filename, string $content): void
    {
        if (!IMPORT_DEBUG) {
            return;
        }
        $path = APP_ROOT . '/' . $filename;
        file_put_contents($path, date('c') . "\n" . $content . "\n---\n", FILE_APPEND);
    }
}
