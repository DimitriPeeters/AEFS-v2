<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\RedirectResponse;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;

abstract class BaseController
{
    protected function view(string $view, array $data = []): Response
    {
        extract($data, EXTR_SKIP);

        ob_start();

        require dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        return new Response((string) ob_get_clean());
    }

    protected function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

    protected function request(): Request
    {
        return Request::capture();
    }

    protected function input(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->input($key, $default);
    }

    protected function post(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->post($key, $default);
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    protected function success(string $message): void
    {
        $this->flash('success', $message);
    }

    protected function error(string $message): void
    {
        $this->flash('danger', $message);
    }
}