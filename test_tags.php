<?php
foreach (['app/Controllers/POSController.php', 'app/Models/Product.php', 'app/Models/Inventory.php'] as $f) {
    echo $f . ': ' . (strpos(file_get_contents($f), '?>') !== false ? 'YES' : 'NO') . "\n";
}
