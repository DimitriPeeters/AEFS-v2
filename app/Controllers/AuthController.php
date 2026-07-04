<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\RedirectResponse;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use App\Services\AuthenticationService;

final class AuthController
{
    public function __construct(
        private AuthenticationService $auth
    ) {
    }

    public function login(Request $request): Response
    {
        if ($this->auth->check()) {
            return new RedirectResponse('/dashboard');
        }

        ob_start();

        require dirname(__DIR__, 2) . '/resources/views/auth/login.php';

        return new Response((string) ob_get_clean());
    }

    public function authenticate(Request $request): Response
    {
        $email = trim((string) $request->post('email'));
        $password = (string) $request->post('password');

        if ($email === '' || $password === '') {
           return new RedirectResponse('/aefs-v2/public/login?error=missing');
        }

        if (!$this->auth->attempt($email, $password)) {
           return new RedirectResponse('/aefs-v2/public/login?error=invalid');
        }

        return new RedirectResponse('/aefs-v2/public/dashboard');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();

        return new RedirectResponse('/aefs-v2/public/login?logout=1');
    }
}