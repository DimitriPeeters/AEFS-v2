<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use AEFS\Core\Application;
use AEFS\Core\Session;

Session::start();

$app = new Application();

$app->run();