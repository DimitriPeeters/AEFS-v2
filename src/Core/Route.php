<?php

declare(strict_types=1);

namespace AEFS\Core;

final class Route
{
    /**
     * @var callable|array{0:class-string,1:string}
     */
    public readonly mixed $action;

    /**
     * @var array<class-string>
     */
    private array $middleware = [];

    /**
     * @var array<string,mixed>
     */
    private array $parameters = [];

    private ?string $name = null;

    /**
     * @param callable|array{0:class-string,1:string} $action
     * @param array<class-string> $middleware
     */
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        mixed $action,
        array $middleware = [],
        ?string $name = null,
    ) {
        $this->action = $action;
        $this->middleware = $middleware;
        $this->name = $name;
    }

    public function middleware(string ...$middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            $middleware
        );

        return $this;
    }

    /**
     * @return array<class-string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array<string,mixed>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function parameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    public function hasParameter(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function matches(string $uri): bool
    {
        return $this->compile($uri) !== null;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function compile(string $uri): ?array
    {
        $parameterNames = [];

        $pattern = preg_replace_callback(
            '#\{([^}]+)\}#',
            static function (array $matches) use (&$parameterNames): string {
                $parameterNames[] = $matches[1];

                return '([^/]+)';
            },
            $this->uri
        );

        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $uri, $matches)) {
            return null;
        }

        array_shift($matches);

        $parameters = [];

        foreach ($parameterNames as $index => $name) {
            $parameters[$name] = urldecode($matches[$index]);
        }

        return $parameters;
    }

    public function allows(string $method): bool
{
    return strtoupper($this->method) === strtoupper($method);
}

public function uri(): string
{
    return $this->uri;
}

public function action(): mixed
{
    return $this->action;
}

public function method(): string
{
    return $this->method;
}


}