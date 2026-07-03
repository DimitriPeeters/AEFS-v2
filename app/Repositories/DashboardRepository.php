<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\Database;
use PDO;

final class DashboardRepository
{
    public function __construct(
        private Database $database
    ) {
    }

    public function statistics(): array
    {
        return [
            'members' => $this->countTable('leden'),
            'users' => $this->countTable('gebruikers'),
            'events' => $this->countTable('evenementen'),
            'shifts' => $this->countTable('event_shifts'),
            'registrations' => $this->countTable('event_inschrijvingen'),
        ];
    }

    public function latestMembers(int $limit = 5): array
    {
        $stmt = $this->database->prepare("
            SELECT
                lid_id,
                voornaam,
                achternaam,
                email,
                gemeente,
                aangemaakt_op
            FROM leden
            ORDER BY aangemaakt_op DESC
            LIMIT :limit
        ");

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function upcomingEvents(int $limit = 5): array
    {
        $stmt = $this->database->prepare("
            SELECT
                event_id,
                titel,
                locatie,
                startdatum,
                einddatum
            FROM evenementen
            WHERE startdatum >= CURDATE()
            ORDER BY startdatum ASC
            LIMIT :limit
        ");

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function openShifts(int $limit = 5): array
    {
        $stmt = $this->database->prepare("
            SELECT
                s.shift_id,
                s.event_id,
                e.titel AS event_titel,
                s.shift_datum,
                s.naam,
                s.starttijd,
                s.eindtijd,
                s.max_personen,
                COUNT(si.id) AS ingevuld
            FROM event_shifts s
            INNER JOIN evenementen e ON e.event_id = s.event_id
            LEFT JOIN shift_inschrijvingen si ON si.shift_id = s.shift_id
            GROUP BY
                s.shift_id,
                s.event_id,
                e.titel,
                s.shift_datum,
                s.naam,
                s.starttijd,
                s.eindtijd,
                s.max_personen
            HAVING ingevuld < s.max_personen
            ORDER BY s.shift_datum ASC, s.starttijd ASC
            LIMIT :limit
        ");

        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countTable(string $table): int
    {
        $allowed = [
            'leden',
            'gebruikers',
            'evenementen',
            'event_shifts',
            'event_inschrijvingen',
        ];

        if (!in_array($table, $allowed, true)) {
            return 0;
        }

        $stmt = $this->database->query("SELECT COUNT(*) FROM {$table}");

        return (int) $stmt->fetchColumn();
    }
}