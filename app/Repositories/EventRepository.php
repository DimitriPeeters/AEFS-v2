<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use App\Mappers\EventMapper;
use App\Models\Event;
use PDO;

final class EventRepository
{
    private const SELECT_EVENT = <<<'SQL'
        SELECT
            e.*,
            (
                SELECT COUNT(*)
                FROM event_inschrijvingen ei
                WHERE ei.event_id = e.event_id
                  AND ei.uitgeschreven_op IS NULL
                  AND ei.status <> 'geweigerd'
            ) AS aantal_inschrijvingen,
            (
                SELECT COUNT(*)
                FROM event_inschrijvingen ei
                WHERE ei.event_id = e.event_id
                  AND ei.uitgeschreven_op IS NULL
                  AND ei.status = 'bevestigd'
            ) AS aantal_bevestigd,
            (
                SELECT COUNT(*)
                FROM event_inschrijvingen ei
                WHERE ei.event_id = e.event_id
                  AND ei.annulatie_aangevraagd_op IS NOT NULL
                  AND ei.uitgeschreven_op IS NULL
            ) AS aantal_annulatieverzoeken
        FROM evenementen e
        SQL;

    private const ORDER_EVENTS = <<<'SQL'
        ORDER BY
            CASE
                WHEN COALESCE(e.einddatum, e.startdatum) >= CURDATE()
                    THEN 0
                ELSE 1
            END ASC,
            CASE
                WHEN COALESCE(e.einddatum, e.startdatum) >= CURDATE()
                    THEN e.startdatum
                ELSE NULL
            END ASC,
            CASE
                WHEN COALESCE(e.einddatum, e.startdatum) < CURDATE()
                    THEN e.startdatum
                ELSE NULL
            END DESC,
            e.titel ASC
        SQL;

    public function __construct(
        private readonly Database $database,
        private readonly EventMapper $mapper
    ) {
    }

    /**
     * Behoudt compatibiliteit met de bestaande shiftmodule.
     *
     * @return Event[]
     */
    public function all(): array
    {
        return $this->allForAdministration();
    }

    /**
     * @return Event[]
     */
    public function allForAdministration(): array
    {
        $statement = $this->database->query(
            self::SELECT_EVENT
            . PHP_EOL
            . self::ORDER_EVENTS
        );

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return Event[]
     */
    public function visibleToMembers(int $memberId): array
    {
        $statement = $this->database->prepare(
            self::SELECT_EVENT
            . PHP_EOL
            . "WHERE e.status <> 'concept'"
            . PHP_EOL
            . 'AND ' . $this->memberVisibilityCondition()
            . PHP_EOL
            . self::ORDER_EVENTS
        );
        $statement->execute(['visible_lid_id' => $memberId]);

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * Behoudt compatibiliteit met bestaande aanroepen.
     *
     * @return Event[]
     */
    public function search(string $zoekterm): array
    {
        return $this->searchForAdministration($zoekterm);
    }

    /**
     * @return Event[]
     */
    public function searchForAdministration(string $zoekterm): array
    {
        return $this->searchByVisibility(
            $zoekterm,
            false
        );
    }

    /**
     * @return Event[]
     */
    public function searchVisibleToMembers(
        string $zoekterm,
        int $memberId
    ): array
    {
        return $this->searchByVisibility(
            $zoekterm,
            true,
            $memberId
        );
    }

    public function find(int $id): ?Event
    {
        return $this->findByVisibility(
            $id,
            false
        );
    }

    public function findVisibleToMembers(
        int $id,
        int $memberId
    ): ?Event
    {
        return $this->findByVisibility(
            $id,
            true,
            $memberId
        );
    }

    /** @return Event[] */
    public function visibleOrManagedForMember(
        int $memberId,
        string $search = ''
    ): array {
        $conditions = [
            '('
            . "(e.status <> 'concept' AND "
            . $this->memberVisibilityCondition()
            . ') OR EXISTS ('
            . 'SELECT 1 FROM event_beheerders eb '
            . 'INNER JOIN leden manager ON manager.lid_id = eb.lid_id '
            . 'WHERE eb.event_id = e.event_id AND eb.lid_id = :manager_lid_id '
            . 'AND manager.actief = 1 '
            . 'AND EXISTS (SELECT 1 FROM gebruikers manager_user '
            . 'WHERE manager_user.lid_id = eb.lid_id '
            . 'AND manager_user.actief = 1 '
            . "AND manager_user.rol = 'lid' "
            . "AND manager_user.goedkeuringsstatus = 'goedgekeurd')"
            . '))',
        ];
        $parameters = [
            'visible_lid_id' => $memberId,
            'manager_lid_id' => $memberId,
        ];

        if (trim($search) !== '') {
            $conditions[] = '('
                . 'e.titel LIKE :zoek_titel '
                . 'OR e.beschrijving LIKE :zoek_beschrijving '
                . 'OR e.locatie LIKE :zoek_locatie'
                . ')';
            $value = '%' . trim($search) . '%';
            $parameters['zoek_titel'] = $value;
            $parameters['zoek_beschrijving'] = $value;
            $parameters['zoek_locatie'] = $value;
        }

        $statement = $this->database->prepare(
            self::SELECT_EVENT
            . PHP_EOL
            . 'WHERE ' . implode(' AND ', $conditions)
            . PHP_EOL
            . self::ORDER_EVENTS
        );
        $statement->execute($parameters);

        return $this->mapRows($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return Event[] */
    public function managedByMember(int $memberId): array
    {
        $statement = $this->database->prepare(
            self::SELECT_EVENT
            . PHP_EOL
            . 'WHERE EXISTS ('
            . 'SELECT 1 FROM event_beheerders eb '
            . 'INNER JOIN leden manager ON manager.lid_id = eb.lid_id '
            . 'WHERE eb.event_id = e.event_id AND eb.lid_id = :lid_id '
            . 'AND manager.actief = 1 '
            . 'AND EXISTS (SELECT 1 FROM gebruikers manager_user '
            . 'WHERE manager_user.lid_id = eb.lid_id '
            . 'AND manager_user.actief = 1 '
            . "AND manager_user.rol = 'lid' "
            . "AND manager_user.goedkeuringsstatus = 'goedgekeurd')"
            . ')'
            . PHP_EOL
            . self::ORDER_EVENTS
        );
        $statement->execute(['lid_id' => $memberId]);

        return $this->mapRows($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function lockForUpdate(int $id): ?Event
    {
        $statement = $this->database->prepare(
            self::SELECT_EVENT
            . PHP_EOL
            . 'WHERE e.event_id = :event_id'
            . PHP_EOL
            . 'LIMIT 1'
            . PHP_EOL
            . 'FOR UPDATE'
        );

        $statement->execute([
            'event_id' => $id,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            ? $this->mapper->fromDatabase($row)
            : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $statement = $this->database->prepare(<<<'SQL'
            INSERT INTO evenementen
            (
                titel,
                beschrijving,
                locatie,
                max_deelnemers,
                startdatum,
                einddatum,
                status,
                werkt_met_groepen,
                groepstoeslag_bedrag,
                aangemaakt_op,
                bijgewerkt_op
            )
            VALUES
            (
                :titel,
                :beschrijving,
                :locatie,
                :max_deelnemers,
                :startdatum,
                :einddatum,
                :status,
                :werkt_met_groepen,
                :groepstoeslag_bedrag,
                NOW(),
                NOW()
            )
            SQL);

        $statement->execute(
            $this->mapper->toDatabase($data)
        );

        return $this->database->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(
        int $id,
        array $data
    ): void {
        $parameters = $this->mapper->toDatabase($data);
        $parameters['event_id'] = $id;

        $statement = $this->database->prepare(<<<'SQL'
            UPDATE evenementen
            SET
                titel = :titel,
                beschrijving = :beschrijving,
                locatie = :locatie,
                max_deelnemers = :max_deelnemers,
                startdatum = :startdatum,
                einddatum = :einddatum,
                status = :status,
                werkt_met_groepen = :werkt_met_groepen,
                groepstoeslag_bedrag = :groepstoeslag_bedrag,
                bijgewerkt_op = NOW()
            WHERE event_id = :event_id
            SQL);

        $statement->execute($parameters);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare(<<<'SQL'
            DELETE FROM evenementen
            WHERE event_id = :event_id
            SQL);

        $statement->execute([
            'event_id' => $id,
        ]);
    }

    /**
     * @return array{inschrijvingen: int, shifts: int}
     */
    public function relatedDataCounts(int $id): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT
                (
                    SELECT COUNT(*)
                    FROM event_inschrijvingen
                    WHERE event_id = :registration_event_id
                ) AS inschrijvingen,
                (
                    SELECT COUNT(*)
                    FROM shifts
                    WHERE event_id = :shift_event_id
                ) AS shifts
            SQL);

        $statement->execute([
            'registration_event_id' => $id,
            'shift_event_id' => $id,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return [
            'inschrijvingen' => (int) (
                $row['inschrijvingen'] ?? 0
            ),
            'shifts' => (int) (
                $row['shifts'] ?? 0
            ),
        ];
    }

    /**
     * @return Event[]
     */
    private function searchByVisibility(
        string $zoekterm,
        bool $membersOnly,
        int $memberId = 0
    ): array {
        $conditions = [
            '('
            . 'e.titel LIKE :zoek_titel '
            . 'OR e.beschrijving LIKE :zoek_beschrijving '
            . 'OR e.locatie LIKE :zoek_locatie'
            . ')',
        ];

        if ($membersOnly) {
            $conditions[] = "e.status <> 'concept'";
            $conditions[] = $this->memberVisibilityCondition();
        }

        $sql = self::SELECT_EVENT
            . PHP_EOL
            . 'WHERE '
            . implode(' AND ', $conditions)
            . PHP_EOL
            . self::ORDER_EVENTS;

        $zoek = '%' . trim($zoekterm) . '%';
        $statement = $this->database->prepare($sql);
        $parameters = [
            'zoek_titel' => $zoek,
            'zoek_beschrijving' => $zoek,
            'zoek_locatie' => $zoek,
        ];

        if ($membersOnly) {
            $parameters['visible_lid_id'] = $memberId;
        }

        $statement->execute($parameters);

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    private function findByVisibility(
        int $id,
        bool $membersOnly,
        int $memberId = 0
    ): ?Event {
        $conditions = [
            'e.event_id = :event_id',
        ];

        if ($membersOnly) {
            $conditions[] = "e.status <> 'concept'";
            $conditions[] = $this->memberVisibilityCondition();
        }

        $sql = self::SELECT_EVENT
            . PHP_EOL
            . 'WHERE '
            . implode(' AND ', $conditions)
            . PHP_EOL
            . 'LIMIT 1';

        $statement = $this->database->prepare($sql);
        $parameters = [
            'event_id' => $id,
        ];

        if ($membersOnly) {
            $parameters['visible_lid_id'] = $memberId;
        }

        $statement->execute($parameters);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            ? $this->mapper->fromDatabase($row)
            : null;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return Event[]
     */
    private function mapRows(array $rows): array
    {
        return array_map(
            fn(array $row): Event => $this->mapper->fromDatabase($row),
            $rows
        );
    }

    private function memberVisibilityCondition(): string
    {
        return <<<'SQL'
            (
                NOT EXISTS (
                    SELECT 1
                    FROM event_groepen visibility_group
                    WHERE visibility_group.event_id = e.event_id
                )
                OR EXISTS (
                    SELECT 1
                    FROM event_groepen visibility_group
                    INNER JOIN leden_groepen member_group
                        ON member_group.groep_id = visibility_group.groep_id
                    WHERE visibility_group.event_id = e.event_id
                      AND member_group.lid_id = :visible_lid_id
                )
            )
            SQL;
    }
}
