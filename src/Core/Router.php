<?php

declare(strict_types=1);

namespace AEFS\Core;

use AEFS\HTTP\Request;
use AEFS\HTTP\Response;
use RuntimeException;

final class Router
{
    private RouteCollection $routes;

    public function __construct(
        ?RouteCollection $routes = null
    ) {
        $this->routes = $routes ?? new RouteCollection();
    }

    public function get(string $uri, mixed $action): Route
    {
        return $this->map(['GET'], $uri, $action);
    }

    public function post(string $uri, mixed $action): Route
    {
        return $this->map(['POST'], $uri, $action);
    }

    public function put(string $uri, mixed $action): Route
    {
        return $this->map(['PUT'], $uri, $action);
    }

    public function patch(string $uri, mixed $action): Route
    {
        return $this->map(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, mixed $action): Route
    {
        return $this->map(['DELETE'], $uri, $action);
    }

    public function options(string $uri, mixed $action): Route
    {
        return $this->map(['OPTIONS'], $uri, $action);
    }

    public function any(string $uri, mixed $action): Route
    {
        return $this->map(
            ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $uri,
            $action
        );
    }

    /**
     * @param array<int,string> $methods
     */
    public function map(array $methods, string $uri, mixed $action): Route
    {
        $route = new Route($methods, $uri, $action);

        $this->routes->add($route);

        return $route;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if (!$route->allows($request->method())) {
                continue;
            }

            if ($route->uri() !== $request->path()) {
                continue;
            }

            return $this->dispatchRoute($route);
        }

        return new Response(
            '404 Not Found',
            404
        );
    }

    public function routes(): RouteCollection
    {
        return $this->routes;
    }

    private function dispatchRoute(Route $route): Response
    {
        $action = $route->action();

        if (is_callable($action)) {
            $response = $action();

            if ($response instanceof Response) {
                return $response;
            }

            return new Response((string) $response);
        }

        if (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;

            $controller = new $class();

            $response = $controller->{$method}();

            if ($response instanceof Response) {
                return $response;
            }

            return new Response((string) $response);
        }

        throw new RuntimeException('Invalid route action.');
    }
}