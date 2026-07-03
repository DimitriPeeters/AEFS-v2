<?php

declare(strict_types=1);

namespace AEFS\Core;

use AEFS\Models\User;

final class Auth
{
    private const SESSION_KEY = 'auth';

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function login(User $user): void
    {
        Session::regenerate();

        Session::set(self::SESSION_KEY, [
            'gebruiker_id' => $user->gebruikerId,
            'lid_id'       => $user->lidId,
            'email'        => $user->email,
            'rol'          => $user->rol,
            'voornaam'     => $user->voornaam,
            'achternaam'   => $user->achternaam,
        ]);
    }

    public static function logout(): void
    {
        if (Session::has(self::SESSION_KEY)) {
            Session::remove(self::SESSION_KEY);
        }

        Session::destroy();
    }

    /**
     * @return array<string,mixed>|null
     */
    public static function user(): ?array
    {
        return Session::get(self::SESSION_KEY);
    }

    public static function id(): ?int
    {
        return self::user()['gebruiker_id'] ?? null;
    }

    public static function memberId(): ?int
    {
        return self::user()['lid_id'] ?? null;
    }

    public static function email(): ?string
    {
        return self::user()['email'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['rol'] ?? null;
    }

    public static function name(): ?string
    {
        $user = self::user();

        if ($user === null) {
            return null;
        }

        return trim(
            ($user['voornaam'] ?? '') .
            ' ' .
            ($user['achternaam'] ?? '')
        );
    }

    public static function isAdmin(): bool
    {
        return self::role() === User::ROLE_ADMIN;
    }

    public static function isMember(): bool
    {
        return self::role() === User::ROLE_MEMBER;
    }
}