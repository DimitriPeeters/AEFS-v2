<?php

declare(strict_types=1);

use AEFS\Controllers\MemberController;
use AEFS\Middleware\AuthMiddleware;

/** @var AEFS\Core\Router $router */

$router
    ->get('/members', [MemberController::class, 'index'])
    ->middleware(AuthMiddleware::class)
    ->name('members.index');

$router
    ->get('/members/create', [MemberController::class, 'create'])
    ->middleware(AuthMiddleware::class)
    ->name('members.create');

$router
    ->post('/members', [MemberController::class, 'store'])
    ->middleware(AuthMiddleware::class)
    ->name('members.store');

$router
    ->get('/members/{id}', [MemberController::class, 'show'])
    ->middleware(AuthMiddleware::class)
    ->name('members.show');

$router
    ->get('/members/{id}/edit', [MemberController::class, 'edit'])
    ->middleware(AuthMiddleware::class)
    ->name('members.edit');

$router
    ->post('/members/{id}/update', [MemberController::class, 'update'])
    ->middleware(AuthMiddleware::class)
    ->name('members.update');

$router
    ->post('/members/{id}/delete', [MemberController::class, 'delete'])
    ->middleware(AuthMiddleware::class)
    ->name('members.delete');