<?php

declare(strict_types=1);

namespace AEFS\Core;

final class RouteCollection
{
    /**
     * @var array<string, array<string, Route>>
     */
    private array $staticRoutes = [];

    /**
     * @var array<string, array<int, Route>>
     */
    private array $dynamicRoutes = [];

    public function add(Route $route): void
    {
        if (str_contains($route->uri, '{')) {

            $this->dynamicRoutes[$route->method][] = $route;

            return;
        }

        $this->staticRoutes[$route->method][$route->uri] = $route;
    }

    public function get(string $method, string $uri): ?Route
    {
        // Exacte route eerst

        if (isset($this->staticRoutes[$method][$uri])) {
            return $this->staticRoutes[$method][$uri];
        }

        // Daarna dynamische routes

        foreach ($this->dynamicRoutes[$method] ?? [] as $route) {

            $parameters = $route->compile($uri);

            if ($parameters === null) {
                continue;
            }

            $route->setParameters($parameters);

            return $route;
        }

        return null;
    }

    /**
     * @return array<string, array<string, Route>>
     */
    public function staticRoutes(): array
    {
        return $this->staticRoutes;
    }

    /**
     * @return array<string, array<int, Route>>
     */
    public function dynamicRoutes(): array
    {
        return $this->dynamicRoutes;
    }

    /**
     * @return array<int, Route>
     */
    public function all(): array
    {
        $routes = [];

        foreach ($this->staticRoutes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                $routes[] = $route;
            }
        }

        foreach ($this->dynamicRoutes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function clear(): void
    {
        $this->staticRoutes = [];
        $this->dynamicRoutes = [];
    }
}