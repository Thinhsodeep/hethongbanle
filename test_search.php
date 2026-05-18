<?php
define('APP_ROOT', __DIR__);
require 'config/database.php';
require 'core/Database.php';
require 'app/Models/Product.php';
require 'app/Models/Inventory.php';

session_start();
$_SESSION['branch_id'] = 1;
$_SESSION['role'] = 'cashier';

$variants = (new Product())->search('tai', null);
$inv = new Inventory();
$result = [];
foreach ($variants as $v) {
    $row = $inv->findInventoryRow(1, $v['variant_id']);
    $v['stock'] = $row ? (int)$row['quantity'] : 0;
    $v['name'] = $v['product_name'];
    $v['sell_price'] = (float)$v['sell_price'];
    $result[] = $v;
}

echo "RESULT:\n";
echo json_encode($result, JSON_PRETTY_PRINT);
