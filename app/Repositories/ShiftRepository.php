<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\BaseRepository;
use AEFS\Core\Database;
use AEFS\Mappers\ShiftMapper;
use AEFS\Models\Shift;

final class ShiftRepository extends BaseRepository
{
    protected string $table = 'shifts';

    protected string $primaryKey = 'id';

    public function __construct(
        Database $database,
        private readonly ShiftMapper $mapper
    ) {
        parent::__construct($database);
    }

    protected function map(array $row): Shift
    {
        return $this->mapper->map($row);
    }

    public function createShift(
        int $eventId,
        string $functie,
        string $shiftDatum,
        string $starttijd,
        string $eindtijd,
        int $maxVrijwilligers
    ): int {
        return $this->insert([
            'event_id' => $eventId,
            'functie' => $functie ?: 'Steward',
            'shift_datum' => $shiftDatum,
            'starttijd' => $starttijd,
            'eindtijd' => $eindtijd,
            'max_vrijwilligers' => $maxVrijwilligers,
            'status' => Shift::STATUS_ACTIEF,
        ]);
    }

    public function updateShift(
        int $id,
        string $functie,
        string $shiftDatum,
        string $starttijd,
        string $eindtijd,
        int $maxVrijwilligers
    ): bool {
        return $this->updateById($id, [
            'functie' => $functie ?: 'Steward',
            'shift_datum' => $shiftDatum,
            'starttijd' => $starttijd,
            'eindtijd' => $eindtijd,
            'max_vrijwilligers' => $maxVrijwilligers,
        ]);
    }

    public function cancel(int $id): bool
    {
        return $this->updateById($id, [
            'status' => Shift::STATUS_GEANNULEERD,
        ]);
    }

    public function activate(int $id): bool
    {
        return $this->updateById($id, [
            'status' => Shift::STATUS_ACTIEF,
        ]);
    }

    public function deleteShift(int $id): bool
    {
        return $this->deleteById($id);
    }

    /**
     * @return Shift[]
     */
    public function findByEvent(int $eventId): array
    {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT
                    s.*,
                    COALESCE(SUM(CASE WHEN sr.status = 'wachtend' THEN 1 ELSE 0 END), 0) AS aantal_wachtend,
                    COALESCE(SUM(CASE WHEN sr.status = 'bevestigd' THEN 1 ELSE 0 END), 0) AS aantal_bevestigd,
                    COALESCE(SUM(CASE WHEN sr.status = 'reserve' THEN 1 ELSE 0 END), 0) AS aantal_reserve
                FROM shifts s
                LEFT JOIN shift_registrations sr
                    ON sr.shift_id = s.id
                WHERE s.event_id = :event_id
                GROUP BY s.id
                ORDER BY s.shift_datum ASC, s.starttijd ASC
            ", [
                'event_id' => $eventId,
            ])
        );
    }

    /**
     * @return Shift[]
     */
    public function findActiveByEvent(int $eventId): array
    {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT
                    s.*,
                    COALESCE(SUM(CASE WHEN sr.status = 'wachtend' THEN 1 ELSE 0 END), 0) AS aantal_wachtend,
                    COALESCE(SUM(CASE WHEN sr.status = 'bevestigd' THEN 1 ELSE 0 END), 0) AS aantal_bevestigd,
                    COALESCE(SUM(CASE WHEN sr.status = 'reserve' THEN 1 ELSE 0 END), 0) AS aantal_reserve
                FROM shifts s
                LEFT JOIN shift_registrations sr
                    ON sr.shift_id = s.id
                WHERE s.event_id = :event_id
                  AND s.status = 'actief'
                GROUP BY s.id
                ORDER BY s.shift_datum ASC, s.starttijd ASC
            ", [
                'event_id' => $eventId,
            ])
        );
    }

    public function findWithStatistics(int $id): ?Shift
    {
        $row = $this->fetch("
            SELECT
                s.*,
                COALESCE(SUM(CASE WHEN sr.status = 'wachtend' THEN 1 ELSE 0 END), 0) AS aantal_wachtend,
                COALESCE(SUM(CASE WHEN sr.status = 'bevestigd' THEN 1 ELSE 0 END), 0) AS aantal_bevestigd,
                COALESCE(SUM(CASE WHEN sr.status = 'reserve' THEN 1 ELSE 0 END), 0) AS aantal_reserve
            FROM shifts s
            LEFT JOIN shift_registrations sr
                ON sr.shift_id = s.id
            WHERE s.id = :id
            GROUP BY s.id
            LIMIT 1
        ", [
            'id' => $id,
        ]);

        return $row ? $this->map($row) : null;
    }

    public function countConfirmed(int $shiftId): int
    {
        return $this->countRegistrationsByStatus($shiftId, 'bevestigd');
    }

    public function countWaiting(int $shiftId): int
    {
        return $this->countRegistrationsByStatus($shiftId, 'wachtend');
    }

    public function countReserve(int $shiftId): int
    {
        return $this->countRegistrationsByStatus($shiftId, 'reserve');
    }

    public function isFull(int $shiftId): bool
    {
        $shift = $this->find($shiftId);

        if (!$shift instanceof Shift) {
            return true;
        }

        return $this->countConfirmed($shiftId) >= $shift->maxVrijwilligers;
    }

    private function countRegistrationsByStatus(
        int $shiftId,
        string $status
    ): int {
        $row = $this->fetch("
            SELECT COUNT(*) AS aantal
            FROM shift_registrations
            WHERE shift_id = :shift_id
              AND status = :status
        ", [
            'shift_id' => $shiftId,
            'status' => $status,
        ]);

        return (int) ($row['aantal'] ?? 0);
    }
}