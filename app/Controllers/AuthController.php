<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Request;
use AEFS\Core\Response;
use AEFS\Services\AuthenticationService;

final class AuthController
{
    public function __construct(
        private AuthenticationService $auth
    ) {
    }

    /**
     * Toon het loginformulier.
     */
    public function login(Request $request): void
    {
        if ($this->auth->check()) {
            Response::redirect('/dashboard');
        }

        require dirname(__DIR__, 2) . '/resources/views/auth/login.php';
    }

    /**
     * Verwerk de login.
     */
    public function authenticate(Request $request): never
    {
        $email = trim((string) $request->post('email'));
        $password = (string) $request->post('password');

        if ($email === '' || $password === '') {
            Response::redirect('/login?error=missing');
        }

        if (!$this->auth->attempt($email, $password)) {
            Response::redirect('/login?error=invalid');
        }

        Response::redirect('/dashboard');
    }

    /**
     * Uitloggen.
     */
    public function logout(Request $request): never
    {
        $this->auth->logout();

        Response::redirect('/login?logout=1');
    }
}