<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\RegistrationController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

/** @var AEFS\Core\Router $router */

$router
    ->get('/login', [AuthController::class, 'login'])
    ->middleware(GuestMiddleware::class)
    ->name('login');

$router
    ->post('/login', [AuthController::class, 'authenticate'])
    ->middleware(GuestMiddleware::class)
    ->name('login.attempt');

$router
    ->get('/register', [RegistrationController::class, 'create'])
    ->middleware(GuestMiddleware::class)
    ->name('register');

$router
    ->post('/register', [RegistrationController::class, 'store'])
    ->middleware(GuestMiddleware::class)
    ->name('register.store');

$router
    ->post('/logout', [AuthController::class, 'logout'])
    ->middleware(AuthMiddleware::class)
    ->name('logout');