<?php

declare(strict_types=1);

namespace AEFS\Middleware;

use AEFS\Core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request): void;
}