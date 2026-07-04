<?php

declare(strict_types=1);

namespace AEFS\HTTP;

use InvalidArgumentException;

final class Cookie
{
    private string $name;

    private string $value;

    private int $expires = 0;

    private string $path = '/';

    private string $domain = '';

    private bool $secure = false;

    private bool $httpOnly = true;

    private string $sameSite = 'Lax';

    public function __construct(
        string $name,
        string $value = ''
    ) {
        if ($name === '') {
            throw new InvalidArgumentException(
                'Cookie name may not be empty.'
            );
        }

        $this->name = $name;
        $this->value = $value;
    }

    public function expires(int $timestamp): self
    {
        $this->expires = $timestamp;

        return $this;
    }

    public function minutes(int $minutes): self
    {
        $this->expires = time() + ($minutes * 60);

        return $this;
    }

    public function forever(): self
    {
        $this->expires = strtotime('+5 years');

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function secure(bool $secure = true): self
    {
        $this->secure = $secure;

        return $this;
    }

    public function httpOnly(bool $httpOnly = true): self
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    public function sameSite(string $sameSite): self
    {
        $allowed = ['Lax', 'Strict', 'None'];

        if (!in_array($sameSite, $allowed, true)) {
            throw new InvalidArgumentException(
                'SameSite must be Lax, Strict or None.'
            );
        }

        $this->sameSite = $sameSite;

        return $this;
    }

    public function send(): bool
    {
        return setcookie(
            $this->name,
            $this->value,
            [
                'expires'  => $this->expires,
                'path'     => $this->path,
                'domain'   => $this->domain,
                'secure'   => $this->secure,
                'httponly' => $this->httpOnly,
                'samesite' => $this->sameSite,
            ]
        );
    }

    public function delete(): bool
    {
        return setcookie(
            $this->name,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => $this->path,
                'domain'   => $this->domain,
                'secure'   => $this->secure,
                'httponly' => $this->httpOnly,
                'samesite' => $this->sameSite,
            ]
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function expiry(): int
    {
        return $this->expires;
    }

    public function session(): self

    public function maxAge(int $seconds): self

    
}