<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/app/Models/Inventory.php';

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRevenueLast7Days(): array
    {
        $stmt = $this->db->query("
            SELECT DATE(created_at) AS date, SUM(final_amount) AS revenue
            FROM orders
            WHERE created_at >= DATE(NOW()) - INTERVAL 6 DAY
              AND status = 'completed'
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        $rows = $stmt->fetchAll();
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data[$date] = 0;
        }
        foreach ($rows as $row) {
            $data[$row['date']] = (float) $row['revenue'];
        }
        return $data;
    }

    /**
     * Tạo đơn bán hàng — schema v2.0 dùng variant_id trong order_items.
     * Trả về order_id hoặc ném exception.
     */
    public function create(array $header, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $total    = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_price'], $items));
            $discount = (float) ($header['discount'] ?? 0);
            $final    = max(0, $total - $discount);
            $payMethod = $header['payment_method'] ?? 'cash';

            $stmt = $this->db->prepare(
                "INSERT INTO orders
                 (branch_id, customer_id, created_by, total_amount, discount, final_amount, payment_method, status, note)
                 VALUES (?,?,?,?,?,?,?,'completed',?)"
            );
            $stmt->execute([
                $header['branch_id'],
                $header['customer_id'] ?: null,
                $header['created_by'],
                $total,
                $discount,
                $final,
                $payMethod,
                $header['note'] ?? null,
            ]);
            $orderId = (int) $this->db->lastInsertId();

            // order_items dùng variant_id (schema v2.0)
            $itemStmt = $this->db->prepare(
                "INSERT INTO order_items (order_id, variant_id, quantity, unit_price)
                 VALUES (?,?,?,?)"
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['variant_id'],
                    $item['quantity'],
                    $item['unit_price'],
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT o.*,
                    b.name AS branch_name,
                    c.full_name AS customer_name, c.phone AS customer_phone,
                    u.full_name AS cashier_name
             FROM orders o
             JOIN branches b ON b.branch_id = o.branch_id
             LEFT JOIN customers c ON c.customer_id = o.customer_id
             JOIN users u ON u.user_id = o.created_by
             WHERE o.order_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT oi.*,
                    p.name AS product_name,
                    pv.sku, pv.color, pv.size, pv.attribute,
                    p.unit
             FROM order_items oi
             JOIN product_variants pv ON pv.variant_id = oi.variant_id
             JOIN products p ON p.product_id = pv.product_id
             WHERE oi.order_id = ?"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getByBranch(?int $branchId = null, ?string $date = null): array
    {
        $sql = "SELECT o.*,
                       b.name AS branch_name,
                       c.full_name AS customer_name,
                       u.full_name AS cashier_name,
                       COUNT(oi.item_id) AS item_count
                FROM orders o
                JOIN branches b ON b.branch_id = o.branch_id
                LEFT JOIN customers c ON c.customer_id = o.customer_id
                JOIN users u ON u.user_id = o.created_by
                LEFT JOIN order_items oi ON oi.order_id = o.order_id
                WHERE 1=1";
        $params = [];
        if ($branchId !== null) {
            $sql .= ' AND o.branch_id = ?';
            $params[] = $branchId;
        }
        if ($date !== null) {
            $sql .= ' AND DATE(o.created_at) = ?';
            $params[] = $date;
        }
        $sql .= ' GROUP BY o.order_id ORDER BY o.created_at DESC LIMIT 200';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function cancel(int $id, int $branchId, array $items): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status='cancelled' WHERE order_id=?");
            $stmt->execute([$id]);

            $inv = new Inventory();
            foreach ($items as $item) {
                $inv->addStock($branchId, $item['variant_id'], $item['quantity']);
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
