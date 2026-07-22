<?php

declare(strict_types=1);

use AEFS\Core\Application;
use AEFS\Core\Config;
use AEFS\Core\Container;
use AEFS\Core\Http\Request;
use AEFS\Core\Kernel;
use AEFS\Core\Router;
use AEFS\Core\Session;
use AEFS\Core\View;
use AEFS\Database\DatabaseManager;
use AEFS\Database\DB;

$basePath = dirname(__DIR__);

$app = new Application($basePath);

$container = $app->container();

$config = new Config($basePath . '/config');

$container->instance(Application::class, $app);
$container->instance(Container::class, $container);
$container->instance(Config::class, $config);

$databaseManager = new DatabaseManager(
    $config->get('database', [])
);

DB::setManager($databaseManager);

$container->instance(
    DatabaseManager::class,
    $databaseManager
);

$container->singleton(Session::class);
$container->instance(
    Request::class,
    Request::capture()
);

require __DIR__
    . DIRECTORY_SEPARATOR
    . 'view.php';

$container->singleton(View::class);
$container->singleton(Router::class);
$container->singleton(Kernel::class);

$router = $container->get(Router::class);

require $basePath . '/routes/web.php';

return $app;