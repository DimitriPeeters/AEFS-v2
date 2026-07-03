<?php

declare(strict_types=1);

namespace AEFS\Models;

use DateTime;

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

    public function displayDate(): string
    {
        if ($this->duurtMeerdereDagen()) {

            return sprintf(
                '%s - %s',
                $this->formatDate($this->startDatum),
                $this->formatDate($this->eindDatum)
            );
        }

        return $this->formatDate($this->startDatum);
    }

    public function isActive(): bool
    {
        return $this->actief;
    }

    public function hasLocation(): bool
    {
        return !empty($this->locatie);
    }

    public function hasDescription(): bool
    {
        return !empty($this->omschrijving);
    }

    public function isPast(): bool
    {
        $today = new DateTime('today');

        $end = new DateTime(
            $this->eindDatum ?? $this->startDatum
        );

        return $end < $today;
    }

    public function isToday(): bool
    {
        $today = (new DateTime())->format('Y-m-d');

        return $today >= $this->startDatum
            && $today <= ($this->eindDatum ?? $this->startDatum);
    }

    public function isFuture(): bool
    {
        $today = new DateTime('today');

        $start = new DateTime($this->startDatum);

        return $start > $today;
    }

    public function durationDays(): int
    {
        $start = new DateTime($this->startDatum);

        $end = new DateTime(
            $this->eindDatum ?? $this->startDatum
        );

        return $start->diff($end)->days + 1;
    }

    private function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        return (new DateTime($date))
            ->format('d/m/Y');
    }
}