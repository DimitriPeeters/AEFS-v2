<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Config;
use AEFS\Core\Container;
use AEFS\Core\Database;
use AEFS\Core\Logger;
use AEFS\Core\Request;
use AEFS\Core\Url;
use AEFS\Core\View;

abstract class BaseController
{
    protected Database $db;

    protected Config $config;

    protected Logger $logger;

    protected Request $request;

    public function __construct()
    {
        $this->db = Container::get(Database::class);

        $this->config = Container::get(Config::class);

        $this->logger = Container::get(Logger::class);

        $this->request = new Request();
    }

    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . Url::to($url));
        exit;
    }

    protected function back(): never
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Url::to('/')));
        exit;
    }

    protected function request(): Request
    {
        return $this->request;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $this->request->post($key, $default);
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->request->query($key, $default);
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