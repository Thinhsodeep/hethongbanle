<?php

declare(strict_types=1);

require_once APP_ROOT . '/app/Middlewares/RoleMiddleware.php';
require_once APP_ROOT . '/app/Models/Product.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Services/GroqClient.php';
require_once APP_ROOT . '/app/Services/ProductImportMapper.php';
require_once APP_ROOT . '/app/Services/ProductImportService.php';
require_once APP_ROOT . '/config/constants.php';

class ProductImportController extends Controller
{
    private const RATE_LIMIT_SECONDS = 30;

    public function index(): void
    {
        RoleMiddleware::require('admin', 'manager');

        $userModel  = new User();
        $branches   = $userModel->getAllBranches();
        $isManager  = ($_SESSION['role'] ?? '') === 'manager';
        if ($isManager) {
            $branchId = (int) ($_SESSION['branch_id'] ?? 0);
            $branches = array_values(array_filter(
                $branches,
                fn ($b) => (int) $b['branch_id'] === $branchId
            ));
        }

        $groqOk = (new GroqClient())->isConfigured();

        $pageTitle = 'Import sản phẩm từ Excel (AI)';
        $this->view('products/import', [
            'categories'      => (new Product())->getAllCategories(),
            'branches'        => $branches,
            'isManager'       => $isManager,
            'sessionBranchId' => (int) ($_SESSION['branch_id'] ?? 0),
            'groqOk'          => $groqOk,
            'maxRows'         => IMPORT_MAX_ROWS,
            'systemFields'    => ProductImportMapper::SYSTEM_FIELDS,
        ]);
    }

    public function mapColumns(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $this->jsonHeaders();

        $body = $this->readJsonBody();
        $headers    = $body['headers'] ?? [];
        $sampleRows = $body['sample_rows'] ?? [];

        if (!is_array($headers) || count($headers) === 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Thiếu headers Excel.'], 400);
            return;
        }

        $headers = array_map(fn ($h) => trim((string) $h), $headers);

        $groq   = new GroqClient();
        $mapper = new ProductImportMapper();

        if ($groq->isConfigured() && !$this->checkRateLimit()) {
            $result = $mapper->mapWithHeuristic($headers);
            $this->jsonResponse([
                'ok'            => true,
                'column_map'    => $result['column_map'],
                'defaults'      => $result['defaults'],
                'confidence'    => $result['confidence'],
                'notes'         => $result['notes'] . ' (đợi 30s để gọi Groq lại)',
                'mapped_via_ai' => false,
            ]);
            return;
        }

        $result = $mapper->mapColumns($headers, is_array($sampleRows) ? $sampleRows : []);
        if ($mapper->lastMapUsedAi) {
            $_SESSION['import_ai_last_call'] = time();
        }

        $this->jsonResponse([
            'ok'            => true,
            'column_map'    => $result['column_map'],
            'defaults'      => $result['defaults'],
            'confidence'    => $result['confidence'],
            'notes'         => $result['notes'],
            'mapped_via_ai' => $mapper->lastMapUsedAi,
        ]);
    }

    public function commit(): void
    {
        RoleMiddleware::require('admin', 'manager');
        $this->jsonHeaders();

        $body = $this->readJsonBody();
        $rows      = $body['rows'] ?? [];
        $columnMap = $body['column_map'] ?? [];
        $branchId  = (int) ($body['branch_id'] ?? 0);
        $options   = is_array($body['options'] ?? null) ? $body['options'] : [];

        if (!is_array($rows) || count($rows) === 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Không có dữ liệu để import.'], 400);
            return;
        }

        if (count($rows) > IMPORT_MAX_ROWS) {
            $this->jsonResponse([
                'ok'      => false,
                'message' => 'Vượt giới hạn ' . IMPORT_MAX_ROWS . ' dòng. Chia file nhỏ hơn.',
            ], 400);
            return;
        }

        $branchId = $this->resolveBranchId($branchId);
        if ($branchId <= 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Chi nhánh không hợp lệ.'], 400);
            return;
        }

        if (!is_array($columnMap) || count($columnMap) === 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Thiếu column_map.'], 400);
            return;
        }

        $hasName = in_array('name', $columnMap, true);
        $hasSku  = in_array('sku', $columnMap, true);
        if (!$hasName || !$hasSku) {
            $this->jsonResponse([
                'ok'      => false,
                'message' => 'Cần map ít nhất cột Tên (name) và Mã SKU (sku).',
            ], 400);
            return;
        }

        try {
            $service = new ProductImportService();
            $result  = $service->commit($rows, $columnMap, $branchId, $options);

            $this->jsonResponse([
                'ok'       => true,
                'imported' => $result['imported'],
                'skipped'  => $result['skipped'],
                'errors'   => $result['errors'],
            ]);
        } catch (Throwable $e) {
            $this->jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function resolveBranchId(int $requested): int
    {
        $role = $_SESSION['role'] ?? '';
        if ($role === 'manager') {
            return (int) ($_SESSION['branch_id'] ?? 0);
        }

        if ($requested <= 0) {
            return 0;
        }

        $branch = (new User())->findBranchById($requested);
        if (!$branch || ($branch['status'] ?? '') !== 'active') {
            return 0;
        }

        return $requested;
    }

    private function checkRateLimit(): bool
    {
        $last = (int) ($_SESSION['import_ai_last_call'] ?? 0);
        return (time() - $last) >= self::RATE_LIMIT_SECONDS;
    }

    /** @return array<string, mixed> */
    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function jsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    /** @param array<string, mixed> $data */
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
