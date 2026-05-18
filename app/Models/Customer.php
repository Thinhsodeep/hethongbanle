<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Customer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(?string $search = null): array
    {
        $sql    = 'SELECT * FROM customers WHERE 1=1';
        $params = [];
        if ($search !== null && $search !== '') {
            $sql .= ' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
            $w = '%' . $search . '%';
            array_push($params, $w, $w, $w);
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY full_name LIMIT 200');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE customer_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByPhone(string $phone): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }

    public function create(array $d): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customers (full_name, phone, email, address) VALUES (?,?,?,?)'
        );
        return $stmt->execute([
            $d['full_name'],
            $d['phone']   ?? null,
            $d['email']   ?? null,
            $d['address'] ?? null,
        ]);
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE customers SET full_name=?, phone=?, email=?, address=? WHERE customer_id=?'
        );
        return $stmt->execute([
            $d['full_name'],
            $d['phone']   ?? null,
            $d['email']   ?? null,
            $d['address'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        // Kiểm tra xem KH có đơn hàng không
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;

        $stmt = $this->db->prepare('DELETE FROM customers WHERE customer_id = ?');
        return $stmt->execute([$id]);
    }

    public function getPurchaseHistory(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT o.order_id, o.branch_id, b.name AS branch_name,
                    o.total_amount, o.discount, o.final_amount,
                    o.payment_method, o.status, o.created_at,
                    COUNT(oi.item_id) AS item_count
             FROM orders o
             JOIN branches b ON b.branch_id = o.branch_id
             LEFT JOIN order_items oi ON oi.order_id = o.order_id
             WHERE o.customer_id = ?
             GROUP BY o.order_id
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function addLoyaltyPoints(int $customerId, int $points): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE customers SET loyalty_points = loyalty_points + ? WHERE customer_id = ?'
        );
        return $stmt->execute([$points, $customerId]);
    }
}
