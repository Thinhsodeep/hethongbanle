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

    public function getByBranch(?int $branchId = null, ?string $stockStatus = null): array
    {
        $sql    = 'SELECT v.*, b.branch_id, i.product_id
                   FROM v_inventory_status v
                   JOIN branches b ON b.name = v.branch_name
                   JOIN products p ON p.sku = v.sku
                   JOIN inventory i ON i.branch_id = b.branch_id AND i.product_id = p.product_id
                   WHERE 1=1';
        $params = [];
        if ($branchId !== null) {
            $sql .= ' AND b.branch_id = ?';
            $params[] = $branchId;
        }
        if ($stockStatus !== null) {
            $sql .= ' AND v.stock_status = ?';
            $params[] = $stockStatus;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY v.stock_status, v.product_name');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSummary(): array
    {
        $row = $this->db->query(
            "SELECT
                (SELECT COUNT(*) FROM branches WHERE status='active') AS total_branches,
                (SELECT COUNT(*) FROM products WHERE status='active') AS total_products,
                COALESCE(SUM(quantity), 0) AS total_stock,
                COALESCE(SUM(quantity > 0 AND quantity <= min_quantity), 0) AS low_stock_count,
                COALESCE(SUM(quantity = 0), 0) AS out_of_stock_count
             FROM inventory"
        )->fetch();
        return $row ?: [];
    }

    public function adjustQuantity(int $branchId, int $productId, int $newQty, int $minQty): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inventory SET quantity=?, min_quantity=?
             WHERE branch_id=? AND product_id=?'
        );
        return $stmt->execute([$newQty, $minQty, $branchId, $productId]);
    }

    public function findInventoryRow(int $branchId, int $productId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, p.name AS product_name, p.sku
             FROM inventory i JOIN products p ON p.product_id = i.product_id
             WHERE i.branch_id = ? AND i.product_id = ?'
        );
        $stmt->execute([$branchId, $productId]);
        return $stmt->fetch();
    }

    public function exportCsv(?int $branchId = null): array
    {
        return $this->getByBranch($branchId);
    }
}
