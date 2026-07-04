<?php

declare(strict_types=1);

namespace AEFS\HTTP;

final class HeaderBag
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * @param array<string, string|array<int,string>> $headers
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->headers;
    }

    public function has(string $name): bool
    {
        return array_key_exists(
            $this->normalize($name),
            $this->headers
        );
    }

    public function get(string $name, ?string $default = null): ?string
    {
        return $this->headers[
            $this->normalize($name)
        ] ?? $default;
    }

    public function set(string $name, string|array $value): void
    {
        $this->headers[$this->normalize($name)] = is_array($value)
            ? implode(', ', $value)
            : $value;
    }

    public function add(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }
    }

    public function remove(string $name): void
    {
        unset($this->headers[$this->normalize($name)]);
    }

    public function replace(array $headers): void
    {
        $this->headers = [];

        $this->add($headers);
    }

    public function clear(): void
    {
        $this->headers = [];
    }

    public function count(): int
    {
        return count($this->headers);
    }

    public function send(): void
    {
        foreach ($this->headers as $name => $value) {
            header($this->format($name) . ': ' . $value, true);
        }
    }

    private function normalize(string $name): string
    {
        return strtolower(trim($name));
    }

    private function format(string $name): string
    {
        return implode(
            '-',
            array_map(
                static fn (string $part): string => ucfirst($part),
                explode('-', $name)
            )
        );
    }

    public function contains(string $name, string $value): bool

    public function first(string $name): ?string
    
}
