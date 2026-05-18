<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Transfer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Danh sách phiếu chuyển kho */
    public function getAll(?int $branchId = null): array
    {
        $sql = "SELECT t.*,
                       bf.name  AS from_branch_name,
                       bt.name  AS to_branch_name,
                       u.full_name AS created_by_name
                FROM stock_transfers t
                JOIN branches bf ON bf.branch_id = t.from_branch_id
                JOIN branches bt ON bt.branch_id = t.to_branch_id
                JOIN users    u  ON u.user_id    = t.created_by
                WHERE 1=1";
        $params = [];
        if ($branchId !== null) {
            $sql .= ' AND (t.from_branch_id = ? OR t.to_branch_id = ?)';
            $params[] = $branchId;
            $params[] = $branchId;
        }
        $sql .= ' ORDER BY t.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT t.*,
                    bf.name  AS from_branch_name,
                    bt.name  AS to_branch_name,
                    u.full_name AS created_by_name
             FROM stock_transfers t
             JOIN branches bf ON bf.branch_id = t.from_branch_id
             JOIN branches bt ON bt.branch_id = t.to_branch_id
             JOIN users    u  ON u.user_id    = t.created_by
             WHERE t.transfer_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Chi tiết sản phẩm trong phiếu — schema v2.0: JOIN product_variants */
    public function getItems(int $transferId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ti.*,
                    p.name AS product_name,
                    pv.sku, pv.color, pv.size, pv.attribute,
                    p.unit
             FROM stock_transfer_items ti
             JOIN product_variants pv ON pv.variant_id = ti.variant_id
             JOIN products p ON p.product_id = pv.product_id
             WHERE ti.transfer_id = ?"
        );
        $stmt->execute([$transferId]);
        return $stmt->fetchAll();
    }

    /** Tạo phiếu chuyển kho mới — schema v2.0: variant_id */
    public function create(array $header, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO stock_transfers
                 (from_branch_id, to_branch_id, created_by, status, note)
                 VALUES (?,?,?,'pending',?)"
            );
            $stmt->execute([
                $header['from_branch_id'],
                $header['to_branch_id'],
                $header['created_by'],
                $header['note'] ?? null,
            ]);
            $transferId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO stock_transfer_items (transfer_id, variant_id, quantity)
                 VALUES (?,?,?)"
            );
            foreach ($items as $item) {
                $itemStmt->execute([$transferId, $item['variant_id'], $item['quantity']]);
            }

            $this->db->commit();
            return $transferId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE stock_transfers SET status = ? WHERE transfer_id = ?'
        );
        return $stmt->execute([$status, $id]);
    }

    /** Kiểm tra tồn kho của variant tại chi nhánh */
    public function getAvailableStock(int $branchId, int $variantId): int
    {
        $stmt = $this->db->prepare(
            'SELECT quantity FROM inventory WHERE branch_id = ? AND variant_id = ?'
        );
        $stmt->execute([$branchId, $variantId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['quantity'] : 0;
    }

    /** Danh sách variants có tồn kho > 0 tại chi nhánh (dùng cho dropdown tạo phiếu) */
    public function getProductsInBranch(int $branchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.variant_id, i.quantity,
                    p.name AS product_name,
                    pv.sku, pv.color, pv.size, pv.attribute,
                    p.unit
             FROM inventory i
             JOIN product_variants pv ON pv.variant_id = i.variant_id
             JOIN products p ON p.product_id = pv.product_id
             WHERE i.branch_id = ? AND i.quantity > 0 AND p.status = 'active' AND pv.status = 'active'
             ORDER BY p.name, pv.sku"
        );
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }
}
