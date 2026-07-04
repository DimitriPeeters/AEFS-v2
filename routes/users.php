<?php

declare(strict_types=1);

use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;

/** @var AEFS\Core\Router $router */

$router->get('/gebruikers',[UserController::class,'index'])
    ->middleware(AuthMiddleware::class);

$router->get('/gebruikers/nieuw',[UserController::class,'create'])
    ->middleware(AuthMiddleware::class);

$router->post('/gebruikers',[UserController::class,'store'])
    ->middleware(AuthMiddleware::class);

$router->get('/gebruikers/{id}',[UserController::class,'show'])
    ->middleware(AuthMiddleware::class);

$router->get('/gebruikers/{id}/bewerken',[UserController::class,'edit'])
    ->middleware(AuthMiddleware::class);

$router->post('/gebruikers/{id}',[UserController::class,'update'])
    ->middleware(AuthMiddleware::class);

$router->post('/gebruikers/{id}/verwijderen',[UserController::class,'delete'])
    ->middleware(AuthMiddleware::class);