<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\Database;
use AEFS\Models\Event;
use PDO;

final class EventRepository
{
    public function __construct(
        private Database $database
    ) {
    }

    /**
     * @return Event[]
     */
    public function all(): array
    {
        $stmt = $this->database->pdo()->query("
            SELECT *
            FROM evenementen
            ORDER BY start_datum DESC
        ");

        $events = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->map($row);
        }

        return $events;
    }

    public function find(int $id): ?Event
    {
        $stmt = $this->database->prepare("
            SELECT *
            FROM evenementen
            WHERE event_id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->map($row);
    }

    public function create(array $data): int
    {
        $stmt = $this->database->prepare("
            INSERT INTO evenementen
            (
                titel,
                omschrijving,
                start_datum,
                eind_datum,
                locatie,
                actief,
                aangemaakt_op,
                bijgewerkt_op
            )
            VALUES
            (
                :titel,
                :omschrijving,
                :start_datum,
                :eind_datum,
                :locatie,
                :actief,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute($data);

        return (int)$this->database
            ->pdo()
            ->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;

        $stmt = $this->database->prepare("
            UPDATE evenementen
            SET
                titel=:titel,
                omschrijving=:omschrijving,
                start_datum=:start_datum,
                eind_datum=:eind_datum,
                locatie=:locatie,
                actief=:actief,
                bijgewerkt_op=NOW()
            WHERE event_id=:id
        ");

        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->database->prepare("
            DELETE
            FROM evenementen
            WHERE event_id=:id
        ");

        $stmt->execute([
            'id' => $id
        ]);
    }

    private function map(array $row): Event
    {
        return new Event(

            eventId: (int)$row['event_id'],

            titel: $row['titel'],

            omschrijving: $row['omschrijving'],

            startDatum: $row['start_datum'],

            eindDatum: $row['eind_datum'],

            locatie: $row['locatie'],

            actief: (bool)$row['actief'],

            aangemaaktOp: $row['aangemaakt_op'],

            bijgewerktOp: $row['bijgewerkt_op']

        );
    }
}