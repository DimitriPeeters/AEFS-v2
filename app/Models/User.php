<?php

declare(strict_types=1);

namespace AEFS\Models;

final class User
{
    public function __construct(
        public readonly int $gebruikerId,
        public readonly int $lidId,
        public readonly string $voornaam,
        public readonly string $achternaam,
        public readonly string $email,
        public readonly string $rol,
        public readonly string $passwordHash,
        public readonly bool $actief,
        public readonly bool $wachtwoordMoetWijzigen,
    ) {
    }

    public function volledigeNaam(): string
    {
        return "{$this->voornaam} {$this->achternaam}";
    }

    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}