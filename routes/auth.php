<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

/** @var AEFS\Core\Router $router */

/*
|--------------------------------------------------------------------------
| Gast routes
|--------------------------------------------------------------------------
*/

$router
    ->get('/login', [AuthController::class, 'login'])
    ->middleware(GuestMiddleware::class)
    ->name('login');

$router
    ->post('/login', [AuthController::class, 'authenticate'])
    ->middleware(GuestMiddleware::class)
    ->name('login.attempt');

/*
|--------------------------------------------------------------------------
| Beveiligde routes
|--------------------------------------------------------------------------
*/

$router
    ->post('/logout', [AuthController::class, 'logout'])
    ->middleware(AuthMiddleware::class)
    ->name('logout');