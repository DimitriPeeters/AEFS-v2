<?php

declare(strict_types=1);

use AEFS\Core\Application;
use AEFS\Core\Config;
use AEFS\Core\Container;
use AEFS\Core\Database\Database;
use AEFS\Core\Http\Request;
use AEFS\Core\Kernel;
use AEFS\Core\Routing\Router;
use AEFS\Core\Session\Session;
use AEFS\Core\View\View;

$basePath = dirname(__DIR__);

$container = new Container();

$config = new Config(
    $basePath . '/config'
);

$container->instance(Container::class, $container);
$container->instance(Config::class, $config);

$container->singleton(Database::class);
$container->singleton(Session::class);
$container->singleton(Request::class, static fn () => Request::capture());
$container->singleton(View::class);
$container->singleton(Router::class);
$container->singleton(Kernel::class);

$app = new Application(
    container: $container,
    kernel: $container->get(Kernel::class),
    config: $config
);

$container->instance(Application::class, $app);

$router = $container->get(Router::class);

require $basePath . '/routes/web.php';

return $app;