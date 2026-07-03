<?php

declare(strict_types=1);

namespace AEFS\Mappers;

use AEFS\Models\Event;

final class EventMapper
{
    public function fromDatabase(array $row): Event
    {
        return new Event(

            eventId: (int) $row['event_id'],

            titel: $row['titel'],

            omschrijving: $row['omschrijving'] ?? null,

            startDatum: $row['start_datum'],

            eindDatum: $row['eind_datum'],

            locatie: $row['locatie'],

            actief: (bool) $row['actief'],

            aangemaaktOp: $row['aangemaakt_op'] ?? null,

            bijgewerktOp: $row['bijgewerkt_op'] ?? null

        );
    }

    public function toDatabase(array $data): array
    {
        return [

            'titel' => trim(
                $data['titel']
            ),

            'omschrijving' => !empty($data['omschrijving'])
                ? trim($data['omschrijving'])
                : null,

            'start_datum' => $data['start_datum'],

            'eind_datum' => !empty($data['eind_datum'])
                ? $data['eind_datum']
                : null,

            'locatie' => !empty($data['locatie'])
                ? trim($data['locatie'])
                : null,

            'actief' => !empty($data['actief']) ? 1 : 0,

        ];
    }
}