<?php

declare(strict_types=1);

use AEFS\Controllers\MemberController;
use AEFS\Middleware\AuthMiddleware;

/** @var AEFS\Core\Router $router */

$router
    ->get('/leden', [MemberController::class, 'index'])
    ->middleware(AuthMiddleware::class)
    ->name('members.index');

$router
    ->get('/leden/nieuw', [MemberController::class, 'create'])
    ->middleware(AuthMiddleware::class)
    ->name('members.create');

$router
    ->post('/leden', [MemberController::class, 'store'])
    ->middleware(AuthMiddleware::class)
    ->name('members.store');

$router
    ->get('/leden/{id}', [MemberController::class, 'show'])
    ->middleware(AuthMiddleware::class)
    ->name('members.show');

$router
    ->get('/leden/{id}/bewerken', [MemberController::class, 'edit'])
    ->middleware(AuthMiddleware::class)
    ->name('members.edit');

$router
    ->post('/leden/{id}', [MemberController::class, 'update'])
    ->middleware(AuthMiddleware::class)
    ->name('members.update');

$router
    ->post('/leden/{id}/verwijderen', [MemberController::class, 'delete'])
    ->middleware(AuthMiddleware::class)
    ->name('members.delete');