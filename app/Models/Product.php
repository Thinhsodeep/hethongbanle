<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/Database.php';

class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function search(string $kw = '', ?int $catId = null, string $status = 'active'): array
    {
        $sql    = "SELECT p.*, c.name AS category_name
                   FROM products p JOIN categories c ON c.category_id = p.category_id
                   WHERE p.status = ?";
        $params = [$status];
        if ($kw !== '') {
            $sql .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
            $w = '%' . $kw . '%';
            array_push($params, $w, $w, $w);
        }
        if ($catId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $catId;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY p.name LIMIT 100');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.name AS category_name
             FROM products p JOIN categories c ON c.category_id = p.category_id
             WHERE p.product_id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products
             (category_id, name, sku, barcode, unit, sell_price, import_price, description, image)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $d['category_id'],
            $d['name'],
            $d['sku'],
            $d['barcode'] ?: null,
            $d['unit'] ?? 'cái',
            $d['sell_price'],
            $d['import_price'] ?? 0,
            $d['description'] ?? null,
            $d['image'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function initInventoryAllBranches(int $productId): void
    {
        $this->db->prepare(
            "INSERT INTO inventory (branch_id, product_id, quantity, min_quantity)
             SELECT branch_id, ?, 0, 5 FROM branches WHERE status = 'active'"
        )->execute([$productId]);
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE products SET category_id=?, name=?, sku=?, barcode=?, unit=?,
             sell_price=?, import_price=?, description=?, status=? WHERE product_id=?"
        );
        return $stmt->execute([
            $d['category_id'],
            $d['name'],
            $d['sku'],
            $d['barcode'] ?: null,
            $d['unit'],
            $d['sell_price'],
            $d['import_price'] ?? 0,
            $d['description'] ?? null,
            $d['status'],
            $id,
        ]);
    }

    public function updateImage(int $id, string $path): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET image=? WHERE product_id=?');
        return $stmt->execute([$path, $id]);
    }

    public function getAllCategories(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
    }

    public function createCategory(array $d): bool
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (?,?)');
        return $stmt->execute([$d['name'], $d['description'] ?? null]);
    }

    public function findCategoryById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE category_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateCategory(int $id, array $d): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET name=?, description=? WHERE category_id=?');
        return $stmt->execute([$d['name'], $d['description'] ?? null, $id]);
    }
}
