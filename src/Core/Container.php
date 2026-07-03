<?php

declare(strict_types=1);

namespace AEFS\Core;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    /**
     * @var array<string,Closure>
     */
    private static array $bindings = [];

    /**
     * @var array<string,object>
     */
    private static array $instances = [];

    public static function bind(
        string $abstract,
        Closure $factory
    ): void {
        self::$bindings[$abstract] = $factory;
    }

    public static function singleton(
        string $abstract,
        Closure $factory
    ): void {

        self::$bindings[$abstract] = function () use (
            $abstract,
            $factory
        ) {

            if (!isset(self::$instances[$abstract])) {

                self::$instances[$abstract] = $factory();

            }

            return self::$instances[$abstract];

        };
    }

    public static function has(string $abstract): bool
    {
        return isset(self::$bindings[$abstract]);
    }

    public static function get(string $abstract): mixed
    {
        if (isset(self::$instances[$abstract])) {

            return self::$instances[$abstract];

        }

        if (isset(self::$bindings[$abstract])) {

            return self::$bindings[$abstract]();

        }

        return self::build($abstract);
    }

    private static function build(string $class): object
    {
        if (!class_exists($class)) {

            throw new RuntimeException(
                "Class {$class} bestaat niet."
            );

        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {

            throw new RuntimeException(
                "{$class} is niet instantieerbaar."
            );

        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {

            return new $class();

        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {

            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType) {

                throw new RuntimeException(
                    "Kan parameter {$parameter->getName()} niet oplossen."
                );

            }

            if ($type->isBuiltin()) {

                if ($parameter->isDefaultValueAvailable()) {

                    $arguments[] = $parameter->getDefaultValue();

                    continue;

                }

                throw new RuntimeException(
                    "Primitive parameter {$parameter->getName()} kan niet geïnjecteerd worden."
                );

            }

            $arguments[] = self::get(
                $type->getName()
            );

        }

        return $reflection->newInstanceArgs(
            $arguments
        );
    }

    public static function clear(): void
    {
        self::$bindings = [];

        self::$instances = [];
    }
}