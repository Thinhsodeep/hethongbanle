<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Inventory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy tồn kho theo chi nhánh — dùng view v_inventory_status (schema v2.0).
     * View giờ có variant_id thay vì product_id.
     */
    public function getByBranch(?int $branchId = null, ?string $stockStatus = null): array
    {
        $sql    = 'SELECT v.* FROM v_inventory_status v WHERE 1=1';
        $params = [];
        if ($branchId !== null) {
            $sql .= ' AND v.branch_id = ?';
            $params[] = $branchId;
        }
        if ($stockStatus !== null) {
            $sql .= ' AND v.stock_status = ?';
            $params[] = $stockStatus;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY v.stock_status, v.product_name, v.sku');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSummary(): array
    {
        $row = $this->db->query(
            "SELECT
                (SELECT COUNT(*) FROM branches WHERE status='active')         AS total_branches,
                (SELECT COUNT(*) FROM products WHERE status='active')         AS total_products,
                (SELECT COUNT(*) FROM product_variants WHERE status='active') AS total_variants,
                COALESCE(SUM(quantity), 0)                                    AS total_stock,
                COALESCE(SUM(quantity > 0 AND quantity <= min_quantity), 0)   AS low_stock_count,
                COALESCE(SUM(quantity = 0), 0)                                AS out_of_stock_count
             FROM inventory"
        )->fetch();
        return $row ?: [];
    }

    /**
     * Điều chỉnh tồn kho thủ công — dùng variant_id (schema v2.0).
     */
    public function adjustQuantity(int $branchId, int $variantId, int $newQty, int $minQty): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory SET quantity=?, min_quantity=?
             WHERE branch_id=? AND variant_id=?'
        );
        return $stmt->execute([$newQty, $minQty, $branchId, $variantId]);
    }

    /**
     * Tìm 1 dòng tồn kho theo branch + variant_id (schema v2.0).
     */
    public function findInventoryRow(int $branchId, int $variantId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, pv.sku, p.name AS product_name, p.unit
             FROM inventory i
             JOIN product_variants pv ON pv.variant_id = i.variant_id
             JOIN products p ON p.product_id = pv.product_id
             WHERE i.branch_id = ? AND i.variant_id = ?'
        );
        $stmt->execute([$branchId, $variantId]);
        return $stmt->fetch();
    }

    public function exportCsv(?int $branchId = null): array
    {
        return $this->getByBranch($branchId);
    }

    /**
     * Trừ tồn kho theo variant_id (dùng cho chuyển kho xuất / POS).
     */
    public function deductStock(int $branchId, int $variantId, int $qty): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory SET quantity = GREATEST(0, quantity - ?)
             WHERE branch_id = ? AND variant_id = ?'
        );
        return $stmt->execute([$qty, $branchId, $variantId]);
    }

    /**
     * Cộng tồn kho theo variant_id (dùng cho chuyển kho nhận / nhập hàng).
     * Upsert: nếu chưa có row thì tạo mới.
     */
    public function addStock(int $branchId, int $variantId, int $qty): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO inventory (branch_id, variant_id, quantity, min_quantity)
             VALUES (?, ?, ?, 5)
             ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
        );
        return $stmt->execute([$branchId, $variantId, $qty]);
    }
}
