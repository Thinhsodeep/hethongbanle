<?php
require_once 'config/database.php';
require_once 'core/Database.php';
$db = Database::getInstance();
$sql = 'SELECT p.product_id, p.name AS product_name, p.unit, p.category_id, c.name AS category_name, pv.variant_id, pv.sku, pv.barcode, pv.color, pv.size, pv.attribute, pv.sell_price, pv.import_price, pv.status AS variant_status FROM products p JOIN categories c ON c.category_id = p.category_id JOIN product_variants pv ON pv.product_id = p.product_id WHERE p.status = ? AND pv.status = ? AND (p.name LIKE ? OR pv.sku LIKE ? OR pv.barcode LIKE ?) ORDER BY p.name, pv.sku LIMIT 200';
$stmt = $db->prepare($sql);
$stmt->execute(['active', 'active', '%SKU-EL-001%', '%SKU-EL-001%', '%SKU-EL-001%']);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
