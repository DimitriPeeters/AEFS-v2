<?php

declare(strict_types=1);

use App\Controllers\EventController;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;

/** @var AEFS\Core\Router $router */

$router
    ->get('/events', [EventController::class, 'index'])
    ->middleware(AuthMiddleware::class)
    ->name('events.index');

$router
    ->get('/events/create', [EventController::class, 'create'])
    ->middleware(
        AuthMiddleware::class,
        AdminMiddleware::class
    )
    ->name('events.create');

$router
    ->post('/events/store', [EventController::class, 'store'])
    ->middleware(
        AuthMiddleware::class,
        AdminMiddleware::class
    )
    ->name('events.store');

$router
    ->get('/events/{id}', [EventController::class, 'show'])
    ->middleware(AuthMiddleware::class)
    ->name('events.show');

$router
    ->get('/events/{id}/edit', [EventController::class, 'edit'])
    ->middleware(
        AuthMiddleware::class,
        AdminMiddleware::class
    )
    ->name('events.edit');

$router
    ->post('/events/{id}/update', [EventController::class, 'update'])
    ->middleware(
        AuthMiddleware::class,
        AdminMiddleware::class
    )
    ->name('events.update');

$router
    ->post('/events/{id}/delete', [EventController::class, 'destroy'])
    ->middleware(
        AuthMiddleware::class,
        AdminMiddleware::class
    )
    ->name('events.destroy');
