<?php

declare(strict_types=1);

namespace AEFS\Core;

final class Url
{
    public static function base(): string
    {
        $config = require dirname(__DIR__, 2) . '/config/app.php';

        if (!empty($config['base_url'])) {
            return rtrim($config['base_url'], '/');
        }

        $script = $_SERVER['SCRIPT_NAME'] ?? '';

        return rtrim(str_replace(
            '\\',
            '/',
            dirname($script)
        ), '/');
    }

    public static function to(string $path = ''): string
    {
        $path = '/' . ltrim($path, '/');

        return self::base() . $path;
    }
}