<?php

declare(strict_types=1);

namespace AEFS\Core;

final class Kernel
{
    public function handle(): void
    {
        $router = Container::get(Router::class);

        require dirname(__DIR__, 2) . '/routes/web.php';


        $router->dispatch(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
        );
    }
}