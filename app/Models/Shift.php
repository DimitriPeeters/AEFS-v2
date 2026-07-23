<?php

declare(strict_types=1);

namespace App\Models;

final class Shift
{
    public const STATUS_ACTIEF = 'actief';
    public const STATUS_GEANNULEERD = 'geannuleerd';

    public function __construct(
        public readonly ?int $id,
        public readonly int $eventId,
        public readonly string $functie,
        public readonly string $shiftDatum,
        public readonly string $starttijd,
        public readonly string $eindtijd,
        public readonly int $maxVrijwilligers,
        public readonly string $status,
        public readonly ?string $aangemaaktOp = null,
        public readonly ?string $bijgewerktOp = null,
        public readonly int $aantalWachtend = 0,
        public readonly int $aantalBevestigd = 0,
        public readonly int $aantalReserve = 0
    ) {
    }

    public function isActief(): bool
    {
        return $this->status === self::STATUS_ACTIEF;
    }

    public function beschikbarePlaatsen(): int
    {
        return max(0, $this->maxVrijwilligers - $this->aantalBevestigd);
    }

    public function isVolzet(): bool
    {
        return $this->beschikbarePlaatsen() <= 0;
    }
}