<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Core\Auth;
use AEFS\Models\User;
use AEFS\Repositories\UserRepository;

final class AuthenticationService
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    /**
     * Probeer een gebruiker aan te melden.
     */
    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (!$user->isActive()) {
            return false;
        }

        if (!password_verify($password, $user->passwordHash)) {
            return false;
        }

        Auth::login($user);

        return true;
    }

    /**
     * Uitloggen.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Is een gebruiker aangemeld?
     */
    public function check(): bool
    {
        return Auth::check();
    }

    /**
     * Haal de huidige gebruiker op.
     */
    public function user(): ?User
    {
        if (!Auth::check()) {
            return null;
        }

        $session = Auth::user();

        if ($session === null) {
            return null;
        }

        return $this->users->findByEmail($session['email']);
    }

    /**
     * Is de gebruiker administrator?
     */
    public function isAdmin(): bool
    {
        return Auth::isAdmin();
    }

    /**
     * Is de gebruiker een gewoon lid?
     */
    public function isMember(): bool
    {
        return Auth::isMember();
    }

    /**
     * Vereis administratorrechten.
     */
    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            abort(403);
        }
    }

    /**
     * Vereis een aangemelde gebruiker.
     */
    public function requireAuthentication(): void
    {
        if (!$this->check()) {
            abort(401);
        }
    }
}