<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Supplier
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(string $status = 'all'): array
    {
        if ($status === 'all') {
            return $this->db->query('SELECT * FROM suppliers ORDER BY name')->fetchAll();
        }
        $stmt = $this->db->prepare("SELECT * FROM suppliers WHERE status = ? ORDER BY name");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM suppliers WHERE supplier_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $d): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO suppliers (name, phone, email, address) VALUES (?,?,?,?)'
        );
        return $stmt->execute([$d['name'], $d['phone'] ?? null, $d['email'] ?? null, $d['address'] ?? null]);
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE suppliers SET name=?, phone=?, email=?, address=?, status=? WHERE supplier_id=?'
        );
        return $stmt->execute([
            $d['name'],
            $d['phone']   ?? null,
            $d['email']   ?? null,
            $d['address'] ?? null,
            $d['status']  ?? 'active',
            $id,
        ]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE suppliers SET status = IF(status='active','inactive','active') WHERE supplier_id = ?"
        );
        return $stmt->execute([$id]);
    }
}
