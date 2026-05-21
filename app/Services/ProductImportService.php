<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/app/Models/Product.php';
require_once APP_ROOT . '/app/Models/Inventory.php';
require_once APP_ROOT . '/config/constants.php';

final class ProductImportService
{
    private Product $product;
    private Inventory $inventory;

    public function __construct()
    {
        $this->product   = new Product();
        $this->inventory = new Inventory();
    }

    /**
     * @param list<array<string, string>> $rows Mỗi phần tử: header => cell value
     * @param array<string, string> $columnMap header => system field
     * @param array<string, mixed> $options default_category_id, defaults
     * @return array{imported: int, skipped: int, errors: list<array{row: int, message: string}>}
     */
    public function commit(
        array $rows,
        array $columnMap,
        int $branchId,
        array $options = []
    ): array {
        $defaultCategoryId = (int) ($options['default_category_id'] ?? 0);
        $defaults          = is_array($options['defaults'] ?? null) ? $options['defaults'] : [];
        $seenSkus          = [];
        $imported          = 0;
        $skipped           = 0;
        $errors            = [];

        $db = Database::getInstance();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Excel row (header = 1)
            try {
                $mapped = $this->mapRow($row, $columnMap, $defaults);

                $name = trim($mapped['name'] ?? '');
                $sku  = trim($mapped['sku'] ?? '');

                if ($name === '') {
                    throw new InvalidArgumentException('Thiếu tên sản phẩm');
                }
                if ($sku === '') {
                    throw new InvalidArgumentException('Thiếu mã SKU');
                }
                if (isset($seenSkus[$sku])) {
                    throw new InvalidArgumentException("SKU trùng trong file: $sku");
                }
                if ($this->product->skuExists($sku)) {
                    throw new InvalidArgumentException("SKU đã tồn tại trong hệ thống: $sku");
                }

                $categoryId = $this->resolveCategoryId(
                    $mapped['category_name'] ?? '',
                    $defaultCategoryId
                );

                $db->beginTransaction();

                $productId = $this->product->create([
                    'category_id' => $categoryId,
                    'name'        => $name,
                    'unit'        => $mapped['unit'] ?? 'cái',
                    'description' => $mapped['description'] ?? null,
                    'image'       => null,
                ]);

                $variantId = $this->product->createVariant($productId, [
                    'sku'          => $sku,
                    'barcode'      => $mapped['barcode'] ?? '',
                    'color'        => $mapped['color'] ?? '',
                    'size'         => $mapped['size'] ?? '',
                    'attribute'    => $mapped['attribute'] ?? '',
                    'sell_price'   => $this->parsePrice($mapped['sell_price'] ?? '0'),
                    'import_price' => $this->parsePrice($mapped['import_price'] ?? '0'),
                ]);

                $this->product->initInventoryAllBranches($variantId);

                $qty = (int) ($mapped['quantity'] ?? 0);
                if ($qty > 0) {
                    $this->inventory->adjustQuantity($branchId, $variantId, $qty, 5);
                }

                $db->commit();
                $seenSkus[$sku] = true;
                $imported++;
            } catch (Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $skipped++;
                $errors[] = ['row' => $rowNum, 'message' => $e->getMessage()];
            }
        }

        return [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ];
    }

    /**
     * @param array<string, string> $row
     * @param array<string, string> $columnMap
     * @param array<string, string> $defaults
     * @return array<string, string>
     */
    public function mapRow(array $row, array $columnMap, array $defaults = []): array
    {
        $mapped = [];
        foreach ($columnMap as $header => $field) {
            if ($field === '_skip' || !isset($row[$header])) {
                continue;
            }
            $value = trim((string) $row[$header]);
            if ($value === '') {
                continue;
            }
            $mapped[$field] = $value;
        }

        foreach ($defaults as $k => $v) {
            if (!isset($mapped[$k]) && is_string($v) && $v !== '') {
                $mapped[$k] = $v;
            }
        }

        return $mapped;
    }

    /**
     * @param list<array<string, string>> $rows
     * @param array<string, string> $columnMap
     * @param array<string, string> $defaults
     * @return list<array<string, string>>
     */
    public function previewRows(array $rows, array $columnMap, array $defaults = [], int $limit = 20): array
    {
        $out = [];
        foreach (array_slice($rows, 0, $limit) as $row) {
            $out[] = $this->mapRow($row, $columnMap, $defaults);
        }
        return $out;
    }

    private function resolveCategoryId(string $categoryName, int $defaultCategoryId): int
    {
        $categoryName = trim($categoryName);
        if ($categoryName !== '') {
            $cat = $this->product->findCategoryByName($categoryName);
            if ($cat) {
                return (int) $cat['category_id'];
            }
            return $this->product->createCategoryReturningId([
                'name'        => $categoryName,
                'description' => 'Tạo tự động khi import Excel',
            ]);
        }

        if ($defaultCategoryId > 0) {
            $cat = $this->product->findCategoryById($defaultCategoryId);
            if ($cat) {
                return $defaultCategoryId;
            }
        }

        throw new InvalidArgumentException('Thiếu danh mục — chọn danh mục mặc định hoặc thêm cột nhóm hàng');
    }

    private function parsePrice(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $s = (string) $value;
        $s = preg_replace('/[^\d.,-]/', '', $s) ?? $s;
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return (float) ($s !== '' ? $s : 0);
    }
}
