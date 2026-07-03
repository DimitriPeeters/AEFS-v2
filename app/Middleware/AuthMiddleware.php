<?php

declare(strict_types=1);

namespace AEFS\Middleware;

use AEFS\Core\Auth;
use AEFS\Core\Request;
use AEFS\Core\Response;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): mixed
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        return $next($request);
    }
}