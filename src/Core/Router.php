<?php

declare(strict_types=1);

namespace AEFS\Core;

use RuntimeException;

final class Router
{
    private RouteCollection $routes;

    private MiddlewarePipeline $pipeline;

    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->pipeline = new MiddlewarePipeline();
    }

    public function get(string $uri, callable|array $action): Route
    {
        return $this->add('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): Route
    {
        return $this->add('POST', $uri, $action);
    }

    public function put(string $uri, callable|array $action): Route
    {
        return $this->add('PUT', $uri, $action);
    }

    public function delete(string $uri, callable|array $action): Route
    {
        return $this->add('DELETE', $uri, $action);
    }

    private function add(
        string $method,
        string $uri,
        callable|array $action
    ): Route {

        $uri = '/' . trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        $route = new Route(
            method: strtoupper($method),
            uri: $uri,
            action: $action
        );

        $this->routes->add($route);

        return $route;
    }

    public function dispatch(
        string $method,
        string $uri
    ): mixed {

        $method = strtoupper($method);
        $uri = $this->normalizeUri($uri);

        $route = $this->routes->get($method, $uri);

        if ($route === null) {
            Response::html(
                '<h1>404 - Pagina niet gevonden</h1>',
                404
            );
        }

        $request = new Request();

        $request->setRouteParameters(
            $route->parameters()
        );

        return $this->pipeline->handle(

            $request,

            $route->getMiddleware(),

            function (Request $request) use ($route) {

                $action = $route->action;

                if (is_callable($action)) {
                    return $action($request);
                }

                if (is_array($action)) {

                    [$controllerClass, $controllerMethod] = $action;

                    $controller = Container::get($controllerClass);

                    if (!method_exists($controller, $controllerMethod)) {
                        throw new RuntimeException(
                            sprintf(
                                'Methode %s::%s bestaat niet.',
                                $controllerClass,
                                $controllerMethod
                            )
                        );
                    }

                    return $controller->$controllerMethod($request);
                }

                throw new RuntimeException(
                    'Ongeldige route actie.'
                );
            }

        );
    }

    private function normalizeUri(string $uri): string
    {
        $uri = parse_url(
            $uri,
            PHP_URL_PATH
        ) ?? '/';

        $basePath = $this->detectBasePath();

        if (
            $basePath !== ''
            && str_starts_with($uri, $basePath)
        ) {
            $uri = substr(
                $uri,
                strlen($basePath)
            );
        }

        $uri = '/' . trim($uri, '/');

        return $uri === '//'
            ? '/'
            : $uri;
    }

    private function detectBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';

        $path = str_replace(
            '\\',
            '/',
            dirname($script)
        );

        return rtrim($path, '/');
    }

    /**
     * @return RouteCollection
     */
    public function routes(): RouteCollection
    {
        return $this->routes;
    }
}