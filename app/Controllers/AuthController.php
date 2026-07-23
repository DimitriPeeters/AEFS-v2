<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\RedirectResponse;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\ViewFactory;
use App\Services\AuthenticationService;

final class AuthController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly AuthenticationService $auth
    ) {
        parent::__construct(
            $views,
            $request
        );
    }

    public function login(): Response
    {
        if ($this->auth->check()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.login');
    }

    public function authenticate(): Response
    {
        $email = trim(
            (string) $this->post('email', '')
        );

        $password = (string) $this->post(
            'password',
            ''
        );

        $remember = (string) $this->post(
            'remember',
            ''
        );

        Session::flash('_old_input', [
            'email' => $email,
            'remember' => $remember,
        ]);

        if ($email === '' || $password === '') {
            Session::flash('_errors', [
                'email' => $email === ''
                    ? ['E-mailadres is verplicht.']
                    : [],
                'password' => $password === ''
                    ? ['Wachtwoord is verplicht.']
                    : [],
            ]);

            $this->error(
                'Vul alle verplichte velden in.'
            );

            return $this->redirect('/login');
        }

        if (!$this->auth->attempt($email, $password)) {
            Session::flash('_errors', [
                'email' => [
                    'De combinatie van e-mailadres en wachtwoord is ongeldig.',
                ],
            ]);

            $this->error(
                'Aanmelden is mislukt.'
            );

            return $this->redirect('/login');
        }

        return $this->redirect('/dashboard');
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        $this->success(
            'Je bent succesvol afgemeld.'
        );

        return $this->redirect('/login');
    }
}