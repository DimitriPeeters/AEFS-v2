<?php

declare(strict_types=1);

namespace AEFS\Models;

final class User
{
    public const ROLE_ADMIN = 'admin';
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
        public readonly string $voornaam,
        public readonly string $achternaam,
    ) {
    }

    public function fullName(): string
    {
        return trim($this->voornaam . ' ' . $this->achternaam);
    }

    public function isActive(): bool
    {
        return $this->actief;
    }

    public function isAdmin(): bool
    {
        return $this->rol === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->rol === self::ROLE_MEMBER;
    }

    public function mustChangePassword(): bool
    {
        return $this->wachtwoordMoetWijzigen;
    }
}