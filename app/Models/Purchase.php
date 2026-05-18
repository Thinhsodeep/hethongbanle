<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Purchase
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?int $branchId = null): array
    {
        $sql = "SELECT po.*,
                       b.name AS branch_name,
                       s.name AS supplier_name,
                       u.full_name AS created_by_name
                FROM purchase_orders po
                JOIN branches  b ON b.branch_id   = po.branch_id
                JOIN suppliers s ON s.supplier_id  = po.supplier_id
                JOIN users     u ON u.user_id      = po.created_by
                WHERE 1=1";
        $params = [];
        if ($branchId !== null) {
            $sql .= ' AND po.branch_id = ?';
            $params[] = $branchId;
        }
        $sql .= ' ORDER BY po.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT po.*,
                    b.name AS branch_name,
                    s.name AS supplier_name,
                    u.full_name AS created_by_name
             FROM purchase_orders po
             JOIN branches  b ON b.branch_id   = po.branch_id
             JOIN suppliers s ON s.supplier_id  = po.supplier_id
             JOIN users     u ON u.user_id      = po.created_by
             WHERE po.po_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Chi tiết đơn nhập — schema v2.0: JOIN product_variants */
    public function getItems(int $poId): array
    {
        $stmt = $this->db->prepare(
            "SELECT poi.*,
                    p.name AS product_name,
                    pv.sku, pv.color, pv.size, pv.attribute,
                    p.unit
             FROM purchase_order_items poi
             JOIN product_variants pv ON pv.variant_id = poi.variant_id
             JOIN products p ON p.product_id = pv.product_id
             WHERE poi.po_id = ?"
        );
        $stmt->execute([$poId]);
        return $stmt->fetchAll();
    }

    /** Tạo đơn nhập — schema v2.0: variant_id */
    public function create(array $header, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $total = array_sum(array_map(
                fn($i) => $i['quantity'] * $i['unit_price'],
                $items
            ));

            $stmt = $this->db->prepare(
                "INSERT INTO purchase_orders
                 (branch_id, supplier_id, created_by, total_amount, status, note)
                 VALUES (?,?,?,?,'pending',?)"
            );
            $stmt->execute([
                $header['branch_id'],
                $header['supplier_id'],
                $header['created_by'],
                $total,
                $header['note'] ?? null,
            ]);
            $poId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                "INSERT INTO purchase_order_items (po_id, variant_id, quantity, unit_price)
                 VALUES (?,?,?,?)"
            );
            foreach ($items as $item) {
                $itemStmt->execute([$poId, $item['variant_id'], $item['quantity'], $item['unit_price']]);
            }

            $this->db->commit();
            return $poId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE purchase_orders SET status = ? WHERE po_id = ?');
        return $stmt->execute([$status, $id]);
    }
}
