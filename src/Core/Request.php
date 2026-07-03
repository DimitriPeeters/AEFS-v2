<?php

declare(strict_types=1);

namespace AEFS\Core;

final class Request
{
    /**
     * @var array<string,mixed>
     */
    private array $routeParameters = [];

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function path(): string
    {
        return parse_url(
            $this->uri(),
            PHP_URL_PATH
        ) ?? '/';
    }

    public function input(
        string $key,
        mixed $default = null
    ): mixed {
        return $_POST[$key]
            ?? $_GET[$key]
            ?? $default;
    }

    public function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $_GET[$key] ?? $default;
    }

    public function post(
        string $key,
        mixed $default = null
    ): mixed {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge(
            $_GET,
            $_POST
        );
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_GET)
            || array_key_exists($key, $_POST);
    }

    public function filled(string $key): bool
    {
        return $this->has($key)
            && trim((string) $this->input($key)) !== '';
    }

    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $data[$key] = $this->input($key);
            }
        }

        return $data;
    }

    public function except(array $keys): array
    {
        $data = $this->all();

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function ajax(): bool
    {
        return (
            $_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''
        ) === 'XMLHttpRequest';
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function header(
        string $name,
        mixed $default = null
    ): mixed {

        $key = 'HTTP_' . strtoupper(
            str_replace('-', '_', $name)
        );

        return $_SERVER[$key] ?? $default;
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public function setRouteParameters(array $parameters): void
    {
        $this->routeParameters = $parameters;
    }

    /**
     * @return array<string,mixed>
     */
    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    public function route(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->routeParameters[$key]
            ?? $default;
    }
}