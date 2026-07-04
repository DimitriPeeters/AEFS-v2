<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Database\DB;

final class AuthenticationService
{
    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function attempt(string $email, string $password): bool
    {
        $user = DB::table('gebruikers')
            ->where('email', '=', $email)
            ->first();

        if ($user === null) {
            return false;
        }

        if (!password_verify($password, (string) $user['wachtwoord_hash'])) {
            return false;
        }

        if ((int) ($user['actief'] ?? 0) !== 1) {
            return false;
        }

        $_SESSION['user_id'] = (int) $user['gebruiker_id'];
        $_SESSION['user_email'] = (string) $user['email'];
        $_SESSION['user_role'] = (string) ($user['rol'] ?? 'lid');

        return true;
    }

    public function logout(): void
    {
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            $_SESSION['user_role']
        );
    }
}