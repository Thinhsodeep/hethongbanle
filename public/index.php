<?php

declare(strict_types=1);

/**
 * Front controller — mọi request đi qua file này.
 */
define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/core/App.php';

(new App())->run();
