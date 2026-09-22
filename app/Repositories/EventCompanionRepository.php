<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use PDO;

final class EventCompanionRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function invitationsForMember(int $memberId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT e.event_id, e.titel, mo.verzonden_op,
                   DATE_ADD(mo.verzonden_op, INTERVAL 7 DAY) AS deadline,
                   (NOW() <= DATE_ADD(mo.verzonden_op, INTERVAL 7 DAY)) AS open
            FROM event_inschrijvingen ei
            INNER JOIN evenementen e ON e.event_id = ei.event_id
            INNER JOIN mailing_ontvangers mo
                ON mo.mailing_id = ei.voorkeur_mailing_id
               AND mo.lid_id = ei.lid_id
               AND mo.status = 'verzonden'
               AND mo.verzonden_op IS NOT NULL
            WHERE ei.lid_id = :lid_id
              AND ei.status = 'bevestigd'
              AND ei.uitgeschreven_op IS NULL
              AND ei.annulatie_aangevraagd_op IS NULL
              AND e.status IN ('gepubliceerd', 'afgesloten')
              AND COALESCE(e.einddatum, e.startdatum) >= CURDATE()
            ORDER BY e.startdatum, e.event_id
            SQL);
        $statement->execute(['lid_id' => $memberId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function deliveredInvitation(int $eventId, int $memberId): ?array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT ei.inschrijving_id, ei.voorkeur_mailing_id, mo.verzonden_op,
                   DATE_ADD(mo.verzonden_op, INTERVAL 7 DAY) AS deadline,
                   (NOW() <= DATE_ADD(mo.verzonden_op, INTERVAL 7 DAY)) AS open
            FROM event_inschrijvingen ei
            INNER JOIN evenementen e ON e.event_id = ei.event_id
            INNER JOIN mailing_ontvangers mo
                ON mo.mailing_id = ei.voorkeur_mailing_id
               AND mo.lid_id = ei.lid_id
               AND mo.status = 'verzonden'
               AND mo.verzonden_op IS NOT NULL
            WHERE ei.event_id = :event_id
              AND ei.lid_id = :lid_id
              AND ei.status = 'bevestigd'
              AND ei.uitgeschreven_op IS NULL
              AND ei.annulatie_aangevraagd_op IS NULL
              AND e.status IN ('gepubliceerd', 'afgesloten')
              AND COALESCE(e.einddatum, e.startdatum) >= CURDATE()
            LIMIT 1
            FOR UPDATE
            SQL);
        $statement->execute([
            'event_id' => $eventId,
            'lid_id' => $memberId,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /** @return array<int, array{lid_id: int, voornaam: string, achternaam: string}> */
    public function confirmedMembers(int $eventId, ?string $shiftDate = null): array
    {
        $dateCondition = $shiftDate === null ? '' : <<<'SQL'
            AND (
                EXISTS (
                    SELECT 1 FROM event_inschrijving_dagen d
                    WHERE d.inschrijving_id = ei.inschrijving_id
                      AND d.datum = :shift_date
                )
                OR (
                    NOT EXISTS (
                        SELECT 1 FROM event_inschrijving_dagen d2
                        WHERE d2.inschrijving_id = ei.inschrijving_id
                    )
                    AND e.startdatum = :legacy_date
                )
            )
            SQL;
        $statement = $this->database->prepare(<<<SQL
            SELECT l.lid_id, l.voornaam, l.achternaam
            FROM event_inschrijvingen ei
            INNER JOIN evenementen e ON e.event_id = ei.event_id
            INNER JOIN leden l ON l.lid_id = ei.lid_id
            WHERE ei.event_id = :event_id
              AND ei.status = 'bevestigd'
              AND ei.uitgeschreven_op IS NULL
              AND ei.annulatie_aangevraagd_op IS NULL
              $dateCondition
            ORDER BY l.achternaam, l.voornaam, l.lid_id
            SQL);
        $params = ['event_id' => $eventId];
        if ($shiftDate !== null) {
            $params['shift_date'] = $shiftDate;
            $params['legacy_date'] = $shiftDate;
        }
        $statement->execute($params);

        return array_map(
            static fn(array $row): array => [
                'lid_id' => (int) $row['lid_id'],
                'voornaam' => (string) $row['voornaam'],
                'achternaam' => (string) $row['achternaam'],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /** @return int[] */
    public function selectedIds(int $eventId, int $memberId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT v.gewenst_lid_id
            FROM event_shift_voorkeuren v
            INNER JOIN event_inschrijvingen ei
                ON ei.event_id = v.event_id AND ei.lid_id = v.lid_id
               AND ei.status = 'bevestigd' AND ei.uitgeschreven_op IS NULL
               AND ei.annulatie_aangevraagd_op IS NULL
            INNER JOIN mailing_ontvangers mo
                ON mo.mailing_id = ei.voorkeur_mailing_id
               AND mo.lid_id = v.lid_id
               AND mo.status = 'verzonden' AND mo.verzonden_op IS NOT NULL
            WHERE v.event_id = :event_id AND v.lid_id = :lid_id
              AND v.voorkeur_mailing_id = ei.voorkeur_mailing_id
            ORDER BY v.gewenst_lid_id
            SQL);
        $statement->execute([
            'event_id' => $eventId,
            'lid_id' => $memberId,
        ]);

        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @param int[] $memberIds */
    public function replaceSelections(
        int $eventId,
        int $memberId,
        int $mailingId,
        array $memberIds
    ): void
    {
        $this->database->execute(<<<'SQL'
            DELETE FROM event_shift_voorkeuren
            WHERE event_id = :event_id
              AND lid_id = :lid_id
              AND voorkeur_mailing_id = :mailing_id
            SQL, [
            'event_id' => $eventId,
            'lid_id' => $memberId,
            'mailing_id' => $mailingId,
        ]);

        $statement = $this->database->prepare(<<<'SQL'
            INSERT INTO event_shift_voorkeuren
                (event_id, lid_id, voorkeur_mailing_id, gewenst_lid_id)
            VALUES (:event_id, :lid_id, :mailing_id, :gewenst_lid_id)
            SQL);
        foreach ($memberIds as $selectedId) {
            $statement->execute([
                'event_id' => $eventId,
                'lid_id' => $memberId,
                'mailing_id' => $mailingId,
                'gewenst_lid_id' => $selectedId,
            ]);
        }
    }

    /** @return array<int, array{lid_id: int, gewenst_lid_id: int}> */
    public function edges(int $eventId): array
    {
        $statement = $this->database->prepare(<<<'SQL'
            SELECT v.lid_id, v.gewenst_lid_id
            FROM event_shift_voorkeuren v
            INNER JOIN event_inschrijvingen source
                ON source.event_id = v.event_id AND source.lid_id = v.lid_id
               AND source.status = 'bevestigd' AND source.uitgeschreven_op IS NULL
               AND source.annulatie_aangevraagd_op IS NULL
            INNER JOIN mailing_ontvangers mo
                ON mo.mailing_id = source.voorkeur_mailing_id
               AND mo.lid_id = source.lid_id
               AND mo.status = 'verzonden' AND mo.verzonden_op IS NOT NULL
            INNER JOIN event_inschrijvingen target
                ON target.event_id = v.event_id
               AND target.lid_id = v.gewenst_lid_id
               AND target.status = 'bevestigd' AND target.uitgeschreven_op IS NULL
               AND target.annulatie_aangevraagd_op IS NULL
            WHERE v.event_id = :event_id
              AND v.voorkeur_mailing_id = source.voorkeur_mailing_id
            ORDER BY v.lid_id, v.gewenst_lid_id
            SQL);
        $statement->execute(['event_id' => $eventId]);

        return array_map(
            static fn(array $row): array => [
                'lid_id' => (int) $row['lid_id'],
                'gewenst_lid_id' => (int) $row['gewenst_lid_id'],
            ],
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }
}
