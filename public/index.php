<?php

declare(strict_types=1);

use AEFS\Core\Application;
use AEFS\Core\Http\Response;

define('AEFS_START', microtime(true));

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

/** @var Response $response */
$response = $app->run();

$response->send();