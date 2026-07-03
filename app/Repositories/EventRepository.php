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
        $stmt = $this->database->query("
            SELECT *
            FROM evenementen
            ORDER BY start_datum DESC, titel ASC
        ");

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return Event[]
     */
    public function search(string $zoekterm): array
    {
        $zoek = '%' . trim($zoekterm) . '%';

        $stmt = $this->database->prepare("
            SELECT *
            FROM evenementen
            WHERE
                titel LIKE :zoek
                OR omschrijving LIKE :zoek
                OR locatie LIKE :zoek
            ORDER BY start_datum DESC, titel ASC
        ");

        $stmt->execute([
            'zoek' => $zoek,
        ]);

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return Event[]
     */
    public function paginate(
        int $page = 1,
        int $perPage = 25
    ): array {

        $page = max(1, $page);

        $offset = ($page - 1) * $perPage;

        $stmt = $this->database->prepare("
            SELECT *
            FROM evenementen
            ORDER BY start_datum DESC, titel ASC
            LIMIT :offset, :limit
        ");

        $stmt->bindValue(
            'offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            'limit',
            $perPage,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function count(): int
    {
        return (int) $this->database
            ->query("
                SELECT COUNT(*)
                FROM evenementen
            ")
            ->fetchColumn();
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
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? $this->map($row)
            : null;
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

        return $this->database->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): void {

        $data['id'] = $id;

        $stmt = $this->database->prepare("
            UPDATE evenementen
            SET
                titel = :titel,
                omschrijving = :omschrijving,
                start_datum = :start_datum,
                eind_datum = :eind_datum,
                locatie = :locatie,
                actief = :actief,
                bijgewerkt_op = NOW()
            WHERE event_id = :id
        ");

        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->database->prepare("
            DELETE
            FROM evenementen
            WHERE event_id = :id
        ");

        $stmt->execute([
            'id' => $id,
        ]);
    }

    private function map(array $row): Event
    {
        return new Event(

            eventId: (int) $row['event_id'],

            titel: $row['titel'],

            omschrijving: $row['omschrijving'],

            startDatum: $row['start_datum'],

            eindDatum: $row['eind_datum'],

            locatie: $row['locatie'],

            actief: (bool) $row['actief'],

            aangemaaktOp: $row['aangemaakt_op'],

            bijgewerktOp: $row['bijgewerkt_op']

        );
    }
}