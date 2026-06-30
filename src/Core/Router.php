<?php

declare(strict_types=1);

namespace AEFS\Core;

use RuntimeException;

final class Router
{
    /**
     * @var array<string,array<string,mixed>>
     */
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->add('POST', $uri, $action);
    }

    public function put(string $uri, callable|array $action): void
    {
        $this->add('PUT', $uri, $action);
    }

    public function delete(string $uri, callable|array $action): void
    {
        $this->add('DELETE', $uri, $action);
    }

    private function add(string $method, string $uri, callable|array $action): void
    {
        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        $this->routes[$method][$uri] = $action;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Tijdelijke oplossing voor localhost
        $basePath = '/aefs-v2/public';

        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        if (!isset($this->routes[$method][$uri])) {
            Response::html('<h1>404 - Pagina niet gevonden</h1>', 404);
        }

        $action = $this->routes[$method][$uri];

        if (is_callable($action)) {
            return $action(new Request());
        }

        if (is_array($action)) {

            [$controllerClass, $controllerMethod] = $action;

            $controller = Container::get($controllerClass);

            return $controller->$controllerMethod();
        }

        throw new RuntimeException('Ongeldige route.');
    }
}