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

    /**
     * Tìm kiếm sản phẩm — JOIN product_variants (schema v2.0).
     * Mỗi dòng trả về = 1 variant (1 SKU cụ thể).
     */
    public function search(string $kw = '', ?int $catId = null, string $status = 'active'): array
    {
        $sql    = "SELECT p.product_id, p.name AS product_name, p.unit, p.category_id, p.image,
                          c.name AS category_name,
                          pv.variant_id, pv.sku, pv.barcode,
                          pv.color, pv.size, pv.attribute,
                          pv.sell_price, pv.import_price,
                          pv.status AS variant_status
                   FROM products p
                   JOIN categories c       ON c.category_id = p.category_id
                   JOIN product_variants pv ON pv.product_id = p.product_id
                   WHERE p.status = ? AND pv.status = ?";
        $params = [$status, $status];

        if ($kw !== '') {
            $sql .= ' AND (p.name LIKE ? OR pv.sku LIKE ? OR pv.barcode LIKE ?)';
            $w = '%' . $kw . '%';
            array_push($params, $w, $w, $w);
        }
        if ($catId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $catId;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY p.name, pv.sku LIMIT 200');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách sản phẩm (1 dòng = 1 product, kèm số lượng variant).
     * Dùng cho trang quản lý sản phẩm.
     */
    public function getAll(string $kw = '', ?int $catId = null): array
    {
        $sql = "SELECT p.product_id, p.name, p.unit, p.description, p.image, p.status,
                       c.name AS category_name, p.category_id,
                       COUNT(pv.variant_id) AS variant_count,
                       MIN(pv.sell_price) AS min_price,
                       MAX(pv.sell_price) AS max_price
                FROM products p
                JOIN categories c ON c.category_id = p.category_id
                LEFT JOIN product_variants pv ON pv.product_id = p.product_id
                WHERE 1=1";
        $params = [];
        if ($kw !== '') {
            $sql .= ' AND (p.name LIKE ? OR pv.sku LIKE ?)';
            $w = '%' . $kw . '%';
            $params[] = $w;
            $params[] = $w;
        }
        if ($catId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $catId;
        }
        $sql .= ' GROUP BY p.product_id ORDER BY p.name LIMIT 500';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Tìm sản phẩm (chỉ thông tin product, không bao gồm variant) */
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

    /** Tìm variant theo variant_id — dùng cho POS store / purchase receive */
    public function findVariantById(int $variantId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT pv.*, p.name AS product_name, p.unit, c.name AS category_name
             FROM product_variants pv
             JOIN products    p ON p.product_id  = pv.product_id
             JOIN categories  c ON c.category_id = p.category_id
             WHERE pv.variant_id = ?"
        );
        $stmt->execute([$variantId]);
        return $stmt->fetch();
    }

    /** Lấy tất cả variants của 1 sản phẩm */
    public function getVariants(int $productId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM product_variants WHERE product_id = ? ORDER BY sku"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /** Tạo sản phẩm mới (chỉ phần chung, không có SKU) */
    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (category_id, name, unit, description, image)
             VALUES (?,?,?,?,?)"
        );
        $stmt->execute([
            $d['category_id'],
            $d['name'],
            $d['unit']        ?? 'cái',
            $d['description'] ?? null,
            $d['image']       ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Tạo variant cho sản phẩm */
    public function createVariant(int $productId, array $d): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO product_variants
             (product_id, sku, barcode, color, size, attribute, sell_price, import_price)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $productId,
            $d['sku'],
            $d['barcode']     ?: null,
            $d['color']       ?: null,
            $d['size']        ?: null,
            $d['attribute']   ?: null,
            $d['sell_price']  ?? 0,
            $d['import_price'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Khởi tạo tồn kho 0 cho variant mới tại tất cả chi nhánh */
    public function initInventoryAllBranches(int $variantId): void
    {
        $this->db->prepare(
            "INSERT INTO inventory (branch_id, variant_id, quantity, min_quantity)
             SELECT branch_id, ?, 0, 5 FROM branches WHERE status = 'active'"
        )->execute([$variantId]);
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE products SET category_id=?, name=?, unit=?, description=?, status=?
             WHERE product_id=?"
        );
        return $stmt->execute([
            $d['category_id'],
            $d['name'],
            $d['unit'],
            $d['description'] ?? null,
            $d['status'],
            $id,
        ]);
    }

    public function updateVariant(int $variantId, array $d): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE product_variants
             SET sku=?, barcode=?, color=?, size=?, attribute=?,
                 sell_price=?, import_price=?, status=?
             WHERE variant_id=?"
        );
        return $stmt->execute([
            $d['sku'],
            $d['barcode']      ?: null,
            $d['color']        ?: null,
            $d['size']         ?: null,
            $d['attribute']    ?: null,
            $d['sell_price'],
            $d['import_price'] ?? 0,
            $d['status'],
            $variantId,
        ]);
    }

    public function deleteVariant(int $variantId): bool
    {
        // Xóa inventory trước, sau đó xóa variant
        $this->db->prepare('DELETE FROM inventory WHERE variant_id = ?')->execute([$variantId]);
        $stmt = $this->db->prepare('DELETE FROM product_variants WHERE variant_id = ?');
        return $stmt->execute([$variantId]);
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

    public function createCategoryReturningId(array $d): int
    {
        $this->createCategory($d);
        return (int) $this->db->lastInsertId();
    }

    public function findCategoryByName(string $name): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE name = ? LIMIT 1');
        $stmt->execute([trim($name)]);
        return $stmt->fetch();
    }

    public function skuExists(string $sku): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM product_variants WHERE sku = ? LIMIT 1');
        $stmt->execute([trim($sku)]);
        return (bool) $stmt->fetchColumn();
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
