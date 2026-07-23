<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

return [
    'paths' => [
        $rootPath
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Views',
    ],

    'namespaces' => [
        'core' => $rootPath
            . DIRECTORY_SEPARATOR
            . 'src'
            . DIRECTORY_SEPARATOR
            . 'Core'
            . DIRECTORY_SEPARATOR
            . 'View'
            . DIRECTORY_SEPARATOR
            . 'Views',
    ],

    'base_url' => '/aefs-v2/public',

    'asset_path' => 'assets',

    'debug' => true,
];