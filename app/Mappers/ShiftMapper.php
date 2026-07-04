<?php

declare(strict_types=1);

namespace AEFS\Mappers;

use AEFS\Models\Shift;

final class ShiftMapper
{
    public function map(array $row): Shift
    {
        return new Shift(
            id: isset($row['id']) ? (int) $row['id'] : null,
            eventId: (int) $row['event_id'],
            functie: (string) ($row['functie'] ?? 'Steward'),
            shiftDatum: (string) $row['shift_datum'],
            starttijd: (string) $row['starttijd'],
            eindtijd: (string) $row['eindtijd'],
            maxVrijwilligers: (int) $row['max_vrijwilligers'],
            status: (string) $row['status'],
            aangemaaktOp: $row['aangemaakt_op'] ?? null,
            bijgewerktOp: $row['bijgewerkt_op'] ?? null,
            aantalWachtend: isset($row['aantal_wachtend']) ? (int) $row['aantal_wachtend'] : 0,
            aantalBevestigd: isset($row['aantal_bevestigd']) ? (int) $row['aantal_bevestigd'] : 0,
            aantalReserve: isset($row['aantal_reserve']) ? (int) $row['aantal_reserve'] : 0
        );
    }
}