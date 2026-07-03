<?php

declare(strict_types=1);

namespace AEFS\Models;

final class Event
{
    public function __construct(

        public readonly int $eventId,

        public readonly string $titel,

        public readonly ?string $omschrijving,

        public readonly string $startDatum,

        public readonly ?string $eindDatum,

        public readonly ?string $locatie,

        public readonly bool $actief,

        public readonly ?string $aangemaaktOp,

        public readonly ?string $bijgewerktOp,

    ) {
    }

    public function duurtMeerdereDagen(): bool
    {
        return $this->eindDatum !== null
            && $this->eindDatum !== $this->startDatum;
    }
}