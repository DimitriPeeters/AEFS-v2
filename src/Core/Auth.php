<?php

declare(strict_types=1);

namespace AEFS\Core;

final class Auth
{
    public static function check(): bool
    {
        return Session::has('user');
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function login(array $user): void
    {
        Session::regenerate();

        Session::set('user', [
            'id'       => $user['id'],
            'naam'     => $user['naam'],
            'email'    => $user['email'],
            'rol'      => $user['rol'],
        ]);
    }

    public static function logout(): void
    {
        Session::remove('user');
        Session::destroy();
    }

    public static function id(): ?int
    {
        return Session::get('user')['id'] ?? null;
    }

    public static function role(): ?string
    {
        return Session::get('user')['rol'] ?? null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }
}