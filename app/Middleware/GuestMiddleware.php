<?php

declare(strict_types=1);

namespace AEFS\Middleware;

use AEFS\Core\Auth;
use AEFS\Core\Request;
use AEFS\Core\Response;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            Response::redirect('/dashboard');
        }
    }
}