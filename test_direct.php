<?php
require_once 'config/config.php';
require_once 'app/Models/User.php';

// Bypass login
$_SESSION['user_id'] = 6;
$_SESSION['role'] = 'cashier';
$_SESSION['branch_id'] = 1;

// Simulate GET params
$_GET['q'] = 'SKU-EL-001';
$_GET['cat'] = '';

ob_start();
require_once 'app/Controllers/POSController.php';
$c = new POSController();
$c->search();
$out = ob_get_clean();
echo "Output length: " . strlen($out) . "\n";
echo "Output: " . $out;
