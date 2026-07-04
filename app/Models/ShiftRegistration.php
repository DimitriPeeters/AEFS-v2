<?php

declare(strict_types=1);

namespace AEFS\Models;

final class ShiftRegistration
{
    public const STATUS_WACHTEND = 'wachtend';
    public const STATUS_BEVESTIGD = 'bevestigd';
    public const STATUS_RESERVE = 'reserve';
    public const STATUS_GEWEIGERD = 'geweigerd';
    public const STATUS_GEANNULEERD = 'geannuleerd';

    public function __construct(
        public readonly ?int $id,
        public readonly int $shiftId,
        public readonly int $lidId,
        public readonly string $status,
        public readonly ?string $aangemaaktOp = null,
        public readonly ?string $bijgewerktOp = null,
        public readonly ?int $goedgekeurdDoor = null,
        public readonly ?string $goedgekeurdOp = null,
        public readonly ?int $geannuleerdDoor = null,
        public readonly ?string $geannuleerdOp = null,
        public readonly ?string $annulatieReden = null,
        public readonly ?string $lidVoornaam = null,
        public readonly ?string $lidAchternaam = null,
        public readonly ?string $lidEmail = null
    ) {
    }

    public function isActief(): bool
    {
        return in_array($this->status, [
            self::STATUS_WACHTEND,
            self::STATUS_BEVESTIGD,
            self::STATUS_RESERVE,
        ], true);
    }
}