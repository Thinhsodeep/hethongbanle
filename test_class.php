<?php
require_once 'config/config.php';
require_once 'core/App.php';
// We just want to see if class_exists('PosController') is true when POSController is loaded
require_once 'app/Controllers/POSController.php';
echo class_exists('PosController') ? 'YES' : 'NO';
