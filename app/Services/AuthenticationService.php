<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use App\Repositories\UserRepository;

final class AuthenticationService
{
    public function __construct(
        private readonly UserRepository $users
    ) {
    }

    public function check(): bool
    {
        return Auth::check();
    }

    public function attempt(
        string $email,
        string $password
    ): bool {
        $email = strtolower(trim($email));

        if ($email === '' || $password === '') {
            return false;
        }

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (!$user->isApproved() || !$user->isActive()) {
            return false;
        }

        if (
            $user->passwordHash === ''
            || !password_verify(
                $password,
                $user->passwordHash
            )
        ) {
            return false;
        }

        Auth::login($user);

        return true;
    }

    public function logout(): void
    {
        Auth::logout();
    }
}