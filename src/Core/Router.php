<?php

declare(strict_types=1);

namespace AEFS\Core;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use RuntimeException;

final class Router
{
    private RouteCollection $routes;

    public function __construct(
        private readonly Container $container,
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
     * @param array<int, string> $methods
     */
    public function map(array $methods, string $uri, mixed $action): Route
    {
        $route = new Route(
            implode('|', $methods),
            $uri,
            $action
        );

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

        return new Response('404 Not Found', 404);
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

        return $response instanceof Response
            ? $response
            : new Response((string) $response);
    }

    if (is_array($action) && count($action) === 2) {
        [$controllerClass, $method] = $action;

        if (!is_string($controllerClass) || !is_string($method)) {
            throw new RuntimeException('Invalid controller action.');
        }

        $controller = $this->container->get($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new RuntimeException(sprintf(
                'Controller method [%s::%s] does not exist.',
                $controllerClass,
                $method
            ));
        }

        $request = $this->container->get(Request::class);

        $response = $controller->{$method}($request);

        return $response instanceof Response
            ? $response
            : new Response((string) $response);
    }

    throw new RuntimeException('Invalid route action.');
}
}