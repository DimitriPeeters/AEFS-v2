<?php

declare(strict_types=1);

use AEFS\Controllers\DashboardController;
use AEFS\Middleware\AuthMiddleware;

/** @var AEFS\Core\Router $router */

$router
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(AuthMiddleware::class)
    ->name('dashboard');