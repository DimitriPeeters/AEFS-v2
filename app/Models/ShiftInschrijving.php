<?php

declare(strict_types=1);

namespace App\Models;

final class ShiftInschrijving
{
    public const STATUS_WACHTEND = 'wachtend';
    public const STATUS_BEVESTIGD = 'bevestigd';
    public const STATUS_RESERVE = 'reserve';
    public const STATUS_GEWEIGERD = 'geweigerd';
    public const STATUS_GEANNULEERD = 'geannuleerd';

    public function __construct(
        public readonly int $id,
        public readonly int $shiftId,
        public readonly int $lidId,
        public readonly string $lidNaam,
        public readonly string $status,
        public readonly ?string $opmerkingLid,
        public readonly ?string $opmerkingIntern,
        public readonly ?int $goedgekeurdDoor,
        public readonly ?string $goedgekeurdOp,
        public readonly ?int $statusGewijzigdDoor,
        public readonly ?string $statusGewijzigdOp,
        public readonly ?int $geannuleerdDoor,
        public readonly ?string $geannuleerdOp,
        public readonly ?string $annulatieReden,
        public readonly ?string $aangemaaktOp
    ) {
    }

    public function isWachtend(): bool
    {
        return $this->status === self::STATUS_WACHTEND;
    }

    public function isBevestigd(): bool
    {
        return $this->status === self::STATUS_BEVESTIGD;
    }

    public function isReserve(): bool
    {
        return $this->status === self::STATUS_RESERVE;
    }

    public function isGeannuleerd(): bool
    {
        return $this->status === self::STATUS_GEANNULEERD;
    }
}