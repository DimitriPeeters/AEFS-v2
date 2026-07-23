<?php

declare(strict_types=1);

use App\Controllers\HomeController;

/** @var AEFS\Core\Router $router */

require __DIR__ . '/auth.php';
require __DIR__ . '/dashboard.php';
require __DIR__ . '/profile.php';
require __DIR__ . '/members.php';
require __DIR__ . '/users.php';

$router->get('/', [HomeController::class, 'index']);
