<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use PDO;

final class EventAccessRepository
{
    public function __construct(
        private readonly Database $database
    ) {
    }

    public function canManage(int $memberId, int $eventId): bool
    {
        if ($memberId <= 0 || $eventId <= 0) {
            return false;
        }

        $statement = $this->database->prepare(<<<'SQL'
            SELECT 1
            FROM event_beheerders eb
            INNER JOIN leden l ON l.lid_id = eb.lid_id
            WHERE eb.event_id = :event_id
              AND eb.lid_id = :lid_id
              AND l.actief = 1
              AND EXISTS (
                  SELECT 1 FROM gebruikers u
                  WHERE u.lid_id = eb.lid_id
                    AND u.actief = 1
                    AND u.rol = 'lid'
                    AND u.goedkeuringsstatus = 'goedgekeurd'
              )
            LIMIT 1
            SQL);
        $statement->execute([
            'event_id' => $eventId,
            'lid_id' => $memberId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function hasManagedEvents(int $memberId): bool
    {
        if ($memberId <= 0) {
            return false;
        }

        $statement = $this->database->prepare(<<<'SQL'
            SELECT 1
            FROM event_beheerders eb
            INNER JOIN leden l ON l.lid_id = eb.lid_id
            WHERE eb.lid_id = :lid_id
              AND l.actief = 1
              AND EXISTS (
                  SELECT 1 FROM gebruikers u
                  WHERE u.lid_id = eb.lid_id
                    AND u.actief = 1
                    AND u.rol = 'lid'
                    AND u.goedkeuringsstatus = 'goedgekeurd'
              )
            LIMIT 1
            SQL);
        $statement->execute(['lid_id' => $memberId]);

        return $statement->fetchColumn() !== false;
    }

    /** @return int[] */
    public function managedEventIds(int $memberId): array
    {
        if ($memberId <= 0) {
            return [];
        }

        $statement = $this->database->prepare(<<<'SQL'
            SELECT eb.event_id
            FROM event_beheerders eb
            INNER JOIN leden l ON l.lid_id = eb.lid_id
            WHERE eb.lid_id = :lid_id
              AND l.actief = 1
              AND EXISTS (
                  SELECT 1 FROM gebruikers u
                  WHERE u.lid_id = eb.lid_id
                    AND u.actief = 1
                    AND u.rol = 'lid'
                    AND u.goedkeuringsstatus = 'goedgekeurd'
              )
            ORDER BY eb.event_id ASC
            SQL);
        $statement->execute(['lid_id' => $memberId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return int[] */
    public function managerIds(int $eventId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT lid_id
            FROM event_beheerders
            WHERE event_id = :event_id
            ORDER BY lid_id ASC
            SQL);
        $statement->execute(['event_id' => $eventId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return int[] */
    public function groupIds(int $eventId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT groep_id
            FROM event_groepen
            WHERE event_id = :event_id
            ORDER BY groep_id ASC
            SQL);
        $statement->execute(['event_id' => $eventId]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return array<int, array{id: int, label: string, email: string}> */
    public function managerOptions(): array
    {
        $rows = $this->database->query(<<<'SQL'
            SELECT
                l.lid_id AS id,
                CONCAT_WS(', ', TRIM(l.achternaam), TRIM(l.voornaam)) AS label,
                LOWER(TRIM(l.email)) AS email
            FROM leden l
            WHERE l.actief = 1
              AND l.email IS NOT NULL
              AND TRIM(l.email) <> ''
              AND EXISTS (
                  SELECT 1
                  FROM gebruikers u
                  WHERE u.lid_id = l.lid_id
                    AND u.actief = 1
                    AND u.rol = 'lid'
                    AND u.goedkeuringsstatus = 'goedgekeurd'
              )
            ORDER BY l.achternaam ASC, l.voornaam ASC, l.lid_id ASC
            SQL)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn(array $row): array => [
                'id' => (int) $row['id'],
                'label' => (string) $row['label'],
                'email' => (string) $row['email'],
            ],
            $rows
        );
    }

    /** @return array<int, array{id: int, label: string}> */
    public function groupOptions(): array
    {
        $rows = $this->database->query(<<<'SQL'
            SELECT groep_id AS id, naam AS label
            FROM groepen
            ORDER BY naam ASC, groep_id ASC
            SQL)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn(array $row): array => [
                'id' => (int) $row['id'],
                'label' => (string) $row['label'],
            ],
            $rows
        );
    }

    /** @param int[] $managerIds */
    public function syncManagers(
        int $eventId,
        array $managerIds,
        ?int $createdBy
    ): void {
        $this->database->execute(
            'DELETE FROM event_beheerders WHERE event_id = :event_id',
            ['event_id' => $eventId]
        );

        $statement = $this->database->prepare(<<<'SQL'
            INSERT INTO event_beheerders
                (event_id, lid_id, aangemaakt_door)
            VALUES
                (:event_id, :lid_id, :aangemaakt_door)
            SQL);

        foreach ($managerIds as $managerId) {
            $statement->execute([
                'event_id' => $eventId,
                'lid_id' => $managerId,
                'aangemaakt_door' => $createdBy,
            ]);
        }
    }

    /** @param int[] $groupIds */
    public function syncGroups(int $eventId, array $groupIds): void
    {
        $this->database->execute(
            'DELETE FROM event_groepen WHERE event_id = :event_id',
            ['event_id' => $eventId]
        );

        $statement = $this->database->prepare(<<<'SQL'
            INSERT INTO event_groepen (event_id, groep_id)
            VALUES (:event_id, :groep_id)
            SQL);

        foreach ($groupIds as $groupId) {
            $statement->execute([
                'event_id' => $eventId,
                'groep_id' => $groupId,
            ]);
        }
    }

    /** @param int[] $ids */
    public function validManagerIds(array $ids): array
    {
        return $this->validIds(
            $ids,
            "SELECT DISTINCT l.lid_id FROM leden l INNER JOIN gebruikers u ON u.lid_id = l.lid_id WHERE l.lid_id IN (%s) AND l.actief = 1 AND u.actief = 1 AND u.rol = 'lid' AND u.goedkeuringsstatus = 'goedgekeurd'"
        );
    }

    /** @param int[] $ids */
    public function validGroupIds(array $ids): array
    {
        return $this->validIds(
            $ids,
            'SELECT groep_id FROM groepen WHERE groep_id IN (%s)'
        );
    }

    /** @param int[] $ids @return int[] */
    private function validIds(array $ids, string $sql): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn(int $id): bool => $id > 0
        )));
        sort($ids);

        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $statement = $this->database->prepare(sprintf($sql, $placeholders));
        $statement->execute($ids);
        $valid = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
        sort($valid);

        return $valid;
    }
}
