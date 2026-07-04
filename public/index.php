<?php

declare(strict_types=1);

use AEFS\Core\Application;

define('AEFS_START', microtime(true));

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->run();