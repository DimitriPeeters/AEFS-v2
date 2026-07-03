<?php

declare(strict_types=1);

namespace AEFS\Models;

final class User
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EVENTMANAGER = 'eventmanager';
    public const ROLE_COORDINATOR = 'coordinator';
    public const ROLE_MEMBER = 'lid';

    public function __construct(
        public readonly int $gebruikerId,
        public readonly int $lidId,
        public readonly string $email,
        public readonly string $rol,
        public readonly bool $actief,
        public readonly bool $mailBlacklist,
        public readonly bool $wachtwoordMoetWijzigen,
        public readonly string $passwordHash,
        public readonly ?string $resetToken,
        public readonly ?string $resetTokenExpires,
        public readonly ?string $laatsteLogin,
        public readonly ?string $laatsteIp,
        public readonly string $voornaam,
        public readonly string $achternaam,
    ) {
    }

    public function fullName(): string
    {
        return trim($this->voornaam . ' ' . $this->achternaam);
    }

    public function initials(): string
    {
        return strtoupper(
            mb_substr($this->voornaam, 0, 1) .
            mb_substr($this->achternaam, 0, 1)
        );
    }

    public function isActive(): bool
    {
        return $this->actief;
    }

    public function isAdmin(): bool
    {
        return $this->rol === self::ROLE_ADMIN;
    }

    public function isEventManager(): bool
    {
        return $this->rol === self::ROLE_EVENTMANAGER;
    }

    public function isCoordinator(): bool
    {
        return $this->rol === self::ROLE_COORDINATOR;
    }

    public function isMember(): bool
    {
        return $this->rol === self::ROLE_MEMBER;
    }

    public function isBlacklisted(): bool
    {
        return $this->mailBlacklist;
    }

    public function mustChangePassword(): bool
    {
        return $this->wachtwoordMoetWijzigen;
    }

    public function hasResetToken(): bool
    {
        return !empty($this->resetToken);
    }

    public function hasLoggedIn(): bool
    {
        return !empty($this->laatsteLogin);
    }

    public function roleLabel(): string
    {
        return match ($this->rol) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_EVENTMANAGER => 'Eventmanager',
            self::ROLE_COORDINATOR => 'Coördinator',
            default => 'Lid',
        };
    }
}