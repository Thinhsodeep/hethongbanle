<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.role_name
             FROM users u JOIN roles r ON r.role_id = u.role_id
             WHERE u.email = ? AND u.status = 'active' LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getAll(?int $branchId = null): array
    {
        $sql = "SELECT u.user_id, u.full_name, u.email, u.phone, u.status,
                       r.role_name, b.name AS branch_name
                FROM users u
                JOIN roles r ON r.role_id = u.role_id
                JOIN branches b ON b.branch_id = u.branch_id";
        $params = [];
        if ($branchId !== null) {
            $sql .= ' WHERE u.branch_id = ?';
            $params[] = $branchId;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY u.full_name');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.role_name FROM users u
             JOIN roles r ON r.role_id = u.role_id WHERE u.user_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $d): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (branch_id, role_id, full_name, email, password_hash, phone)
             VALUES (?,?,?,?,?,?)"
        );
        return $stmt->execute([
            $d['branch_id'],
            $d['role_id'],
            $d['full_name'],
            $d['email'],
            password_hash($d['password'], PASSWORD_BCRYPT),
            $d['phone'] ?? null,
        ]);
    }

    public function update(int $id, array $d): bool
    {
        $sql = "UPDATE users SET branch_id=?, role_id=?, full_name=?, email=?, phone=?, status=?";
        $params = [
            $d['branch_id'],
            $d['role_id'],
            $d['full_name'],
            $d['email'],
            $d['phone'] ?? null,
            $d['status'],
        ];
        if (!empty($d['password'])) {
            $sql .= ', password_hash=?';
            $params[] = password_hash($d['password'], PASSWORD_BCRYPT);
        }
        $sql .= ' WHERE user_id=?';
        $params[] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllBranches(string $status = 'active'): array
    {
        if ($status === 'all') {
            return $this->db->query(
                "SELECT b.*, u.full_name AS manager_name
                 FROM branches b LEFT JOIN users u ON u.user_id = b.manager_id
                 ORDER BY b.name"
            )->fetchAll();
        }
        $stmt = $this->db->prepare(
            "SELECT b.*, u.full_name AS manager_name
             FROM branches b LEFT JOIN users u ON u.user_id = b.manager_id
             WHERE b.status = ? ORDER BY b.name"
        );
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function findBranchById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM branches WHERE branch_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllRoles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY role_id')->fetchAll();
    }

    public function createBranch(array $d): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO branches (name, address, phone) VALUES (?,?,?)'
        );
        return $stmt->execute([$d['name'], $d['address'], $d['phone'] ?? null]);
    }

    public function updateBranch(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE branches SET name=?, address=?, phone=?, manager_id=?, status=? WHERE branch_id=?'
        );
        return $stmt->execute([
            $d['name'],
            $d['address'],
            $d['phone'] ?? null,
            !empty($d['manager_id']) ? (int) $d['manager_id'] : null,
            $d['status'],
            $id,
        ]);
    }

    public function getManagersForSelect(): array
    {
        return $this->db->query(
            "SELECT u.user_id, u.full_name, b.name AS branch_name
             FROM users u JOIN branches b ON b.branch_id = u.branch_id
             JOIN roles r ON r.role_id = u.role_id
             WHERE r.role_name = 'manager' AND u.status = 'active'
             ORDER BY u.full_name"
        )->fetchAll();
    }
}
