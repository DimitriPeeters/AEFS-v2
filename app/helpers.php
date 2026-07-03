<?php

declare(strict_types=1);

use AEFS\Core\Component;

if (!function_exists('component')) {

    function component(
        string $component,
        array $data = []
    ): string {

        return Component::render(
            $component,
            $data
        );

    }

}

if (!function_exists('icon')) {

    function icon(
        string $icon,
        string $class = ''
    ): string {

        return \AEFS\Core\Icon::render(
            $icon,
            $class
        );

    }

}