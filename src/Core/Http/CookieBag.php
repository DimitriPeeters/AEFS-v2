<?php

declare(strict_types=1);

namespace AEFS\HTTP;

final class CookieBag extends ParameterBag
{
    public function has(string $name): bool
    {
        return parent::has($name);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return parent::get($name, $default);
    }

    public function set(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): void {
        parent::set($name, $value);

        setcookie(
            $name,
            $value,
            [
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => $httpOnly,
                'samesite' => $sameSite,
            ]
        );
    }

    public function forever(
        string $name,
        string $value,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): void {
        $this->set(
            $name,
            $value,
            strtotime('+5 years'),
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite
        );
    }

    public function delete(
        string $name,
        string $path = '/',
        string $domain = ''
    ): void {
        parent::remove($name);

        setcookie(
            $name,
            '',
            [
                'expires' => time() - 3600,
                'path'    => $path,
                'domain'  => $domain,
            ]
        );
    }

    public function pull(string $name, mixed $default = null): mixed
    {
        $value = $this->get($name, $default);

        $this->delete($name);

        return $value;
    }

    public function exists(string $name): bool
    {
        return $this->has($name);
    }
}