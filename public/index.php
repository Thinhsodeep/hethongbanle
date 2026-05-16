<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/core/Database.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/App.php';

new App();
