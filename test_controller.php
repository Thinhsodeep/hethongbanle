<?php
define('APP_ROOT', __DIR__);
define('ROOT_PATH', __DIR__);
define('BASE_URL', 'http://localhost/hethongbanle/public');
require_once 'config/database.php';
require_once 'core/Database.php';
require_once 'core/Controller.php';
require_once 'app/Middlewares/RoleMiddleware.php';
require_once 'app/Middlewares/AuthMiddleware.php';
require_once 'app/Models/User.php';
require_once 'app/Controllers/POSController.php';

session_start();
$_SESSION['user_id'] = 2; // Assuming a cashier user
$_SESSION['role'] = 'cashier';
$_SESSION['branch_id'] = 1;

$_GET['q'] = 'tai';
$_GET['cat'] = '';

$pos = new POSController();
$pos->search();
