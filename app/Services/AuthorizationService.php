<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Models\User;
use RuntimeException;

final class AuthorizationService
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EVENTMANAGER = 'eventmanager';
    public const ROLE_MEMBER = 'lid';

    public function requireLogin(?User $user): void
    {
        if ($user === null) {
            throw new RuntimeException('Je bent niet aangemeld.');
        }
    }

    public function requireAdmin(?User $user): void
    {
        $this->requireLogin($user);

        if (!$this->isAdmin($user)) {
            throw new RuntimeException('Onvoldoende rechten.');
        }
    }

    public function requireEventManager(?User $user): void
    {
        $this->requireLogin($user);

        if (!$this->isEventManager($user)) {
            throw new RuntimeException('Onvoldoende rechten.');
        }
    }

    public function requirePlanner(?User $user): void
    {
        $this->requireLogin($user);

        if (
            !$this->isAdmin($user)
            && !$this->isEventManager($user)
        ) {
            throw new RuntimeException('Onvoldoende rechten.');
        }
    }

    public function isAdmin(?User $user): bool
    {
        return $user !== null
            && $user->role === self::ROLE_ADMIN;
    }

    public function isEventManager(?User $user): bool
    {
        return $user !== null
            && (
                $user->role === self::ROLE_ADMIN
                || $user->role === self::ROLE_EVENTMANAGER
            );
    }

    public function isMember(?User $user): bool
    {
        return $user !== null;
    }

    public function canManageShift(?User $user): bool
    {
        return $this->isEventManager($user);
    }

    public function canApproveRegistrations(?User $user): bool
    {
        return $this->isEventManager($user);
    }

    public function canDeleteShift(?User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function canCancelVolunteer(
        ?User $user
    ): bool {
        return $this->isEventManager($user);
    }

    public function canCreateShift(?User $user): bool
    {
        return $this->isEventManager($user);
    }

    public function canEditShift(?User $user): bool
    {
        return $this->isEventManager($user);
    }
}