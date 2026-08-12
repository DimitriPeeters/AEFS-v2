<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use App\Mappers\MemberGroupMapper;
use App\Models\MemberGroup;
use PDO;

final class MemberGroupRepository
{
    public function __construct(
        private readonly Database $database,
        private readonly MemberGroupMapper $mapper
    ) {
    }

    /**
     * @return MemberGroup[]
     */
    public function all(): array
    {
        $statement = $this->database->query(<<<'SQL'
            SELECT
                g.groep_id,
                g.naam,
                g.beschrijving,
                COUNT(lg.lid_id) AS leden_aantal
            FROM groepen AS g
            LEFT JOIN leden_groepen AS lg
                ON lg.groep_id = g.groep_id
            GROUP BY
                g.groep_id,
                g.naam,
                g.beschrijving
            ORDER BY g.naam ASC
            SQL);

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?MemberGroup
    {
        if ($id <= 0) {
            return null;
        }

        $statement = $this->database->prepare(<<<'SQL'
            SELECT
                g.groep_id,
                g.naam,
                g.beschrijving,
                COUNT(lg.lid_id) AS leden_aantal
            FROM groepen AS g
            LEFT JOIN leden_groepen AS lg
                ON lg.groep_id = g.groep_id
            WHERE g.groep_id = :groep_id
            GROUP BY
                g.groep_id,
                g.naam,
                g.beschrijving
            LIMIT 1
            SQL);

        $statement->execute([
            'groep_id' => $id,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row)
            ? $this->mapper->fromDatabase($row)
            : null;
    }

    public function existsByName(string $name): bool
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT COUNT(*)
            FROM groepen
            WHERE naam = :naam
            SQL);

        $statement->execute([
            'naam' => trim($name),
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * @param array{naam: string, beschrijving: string} $data
     */
    public function create(array $data): int
    {
        $statement = $this->database->prepare(<<<'SQL'
            INSERT INTO groepen
            (
                naam,
                beschrijving
            )
            VALUES
            (
                :naam,
                :beschrijving
            )
            SQL);

        $statement->execute([
            'naam' => $data['naam'],
            'beschrijving' => $data['beschrijving'] !== ''
                ? $data['beschrijving']
                : null,
        ]);

        return $this->database->lastInsertId();
    }

    /**
     * @return int[]
     */
    public function memberIds(int $groupId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT lid_id
            FROM leden_groepen
            WHERE groep_id = :groep_id
            ORDER BY lid_id ASC
            SQL);

        $statement->execute([
            'groep_id' => $groupId,
        ]);

        return array_map(
            static fn (mixed $value): int => (int) $value,
            $statement->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    /**
     * @return MemberGroup[]
     */
    public function forMember(int $memberId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT
                g.groep_id,
                g.naam,
                g.beschrijving,
                (
                    SELECT COUNT(*)
                    FROM leden_groepen AS group_members
                    WHERE group_members.groep_id = g.groep_id
                ) AS leden_aantal
            FROM groepen AS g
            INNER JOIN leden_groepen AS lg
                ON lg.groep_id = g.groep_id
            WHERE lg.lid_id = :lid_id
            ORDER BY g.naam ASC
            SQL);

        $statement->execute([
            'lid_id' => $memberId,
        ]);

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @param int[] $memberIds
     */
    public function syncMembers(
        int $groupId,
        array $memberIds
    ): void {
        $delete = $this->database->prepare(<<<'SQL'
            DELETE FROM leden_groepen
            WHERE groep_id = :groep_id
            SQL);

        $delete->execute([
            'groep_id' => $groupId,
        ]);

        if ($memberIds === []) {
            return;
        }

        $insert = $this->database->prepare(<<<'SQL'
            INSERT INTO leden_groepen
            (
                lid_id,
                groep_id
            )
            VALUES
            (
                :lid_id,
                :groep_id
            )
            SQL);

        foreach ($memberIds as $memberId) {
            $insert->execute([
                'lid_id' => $memberId,
                'groep_id' => $groupId,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return MemberGroup[]
     */
    private function mapRows(array $rows): array
    {
        return array_map(
            fn (array $row): MemberGroup => $this->mapper
                ->fromDatabase($row),
            $rows
        );
    }
}
