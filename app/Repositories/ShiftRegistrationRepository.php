<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\BaseRepository;
use AEFS\Core\Database;
use App\Mappers\ShiftRegistrationMapper;
use App\Models\ShiftRegistration;

final class ShiftRegistrationRepository extends BaseRepository
{
    protected string $table = 'shift_registrations';

    protected string $primaryKey = 'id';

    public function __construct(
        Database $database,
        private readonly ShiftRegistrationMapper $mapper
    ) {
        parent::__construct($database);
    }

    protected function map(array $row): ShiftRegistration
    {
        return $this->mapper->map($row);
    }

    public function createRegistration(
        int $shiftId,
        int $lidId,
        string $status = ShiftRegistration::STATUS_WACHTEND
    ): int {
        return $this->insert([
            'shift_id' => $shiftId,
            'lid_id' => $lidId,
            'status' => $status,
        ]);
    }

    public function existsForMember(
        int $shiftId,
        int $lidId
    ): bool {
        $row = $this->fetch("
            SELECT COUNT(*) AS aantal
            FROM shift_registrations
            WHERE shift_id = :shift_id
              AND lid_id = :lid_id
              AND status <> 'geannuleerd'
        ", [
            'shift_id' => $shiftId,
            'lid_id' => $lidId,
        ]);

        return (int) ($row['aantal'] ?? 0) > 0;
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findByShift(int $shiftId): array
    {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT
                    sr.*,
                    l.voornaam AS lid_voornaam,
                    l.achternaam AS lid_achternaam,
                    l.email AS lid_email
                FROM shift_registrations sr
                INNER JOIN leden l
                    ON l.lid_id = sr.lid_id
                WHERE sr.shift_id = :shift_id
                ORDER BY
                    FIELD(sr.status, 'bevestigd', 'wachtend', 'reserve', 'geweigerd', 'geannuleerd'),
                    sr.aangemaakt_op ASC
            ", [
                'shift_id' => $shiftId,
            ])
        );
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findByMember(int $lidId): array
    {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT
                    sr.*
                FROM shift_registrations sr
                WHERE sr.lid_id = :lid_id
                ORDER BY sr.aangemaakt_op DESC
            ", [
                'lid_id' => $lidId,
            ])
        );
    }

    public function approve(
        int $registrationId,
        int $approvedBy
    ): bool {
        return $this->updateById($registrationId, [
            'status' => ShiftRegistration::STATUS_BEVESTIGD,
            'goedgekeurd_door' => $approvedBy,
            'goedgekeurd_op' => date('Y-m-d H:i:s'),
        ]);
    }

    public function reserve(
        int $registrationId,
        int $approvedBy
    ): bool {
        return $this->updateById($registrationId, [
            'status' => ShiftRegistration::STATUS_RESERVE,
            'goedgekeurd_door' => $approvedBy,
            'goedgekeurd_op' => date('Y-m-d H:i:s'),
        ]);
    }

    public function reject(
        int $registrationId,
        int $approvedBy
    ): bool {
        return $this->updateById($registrationId, [
            'status' => ShiftRegistration::STATUS_GEWEIGERD,
            'goedgekeurd_door' => $approvedBy,
            'goedgekeurd_op' => date('Y-m-d H:i:s'),
        ]);
    }

    public function cancel(
        int $registrationId,
        int $cancelledBy,
        ?string $reason = null
    ): bool {
        return $this->updateById($registrationId, [
            'status' => ShiftRegistration::STATUS_GEANNULEERD,
            'geannuleerd_door' => $cancelledBy,
            'geannuleerd_op' => date('Y-m-d H:i:s'),
            'annulatie_reden' => $reason,
        ]);
    }

    public function findNextReserve(int $shiftId): ?ShiftRegistration
    {
        $row = $this->fetch("
            SELECT
                sr.*,
                l.voornaam AS lid_voornaam,
                l.achternaam AS lid_achternaam,
                l.email AS lid_email
            FROM shift_registrations sr
            INNER JOIN leden l
                ON l.lid_id = sr.lid_id
            WHERE sr.shift_id = :shift_id
              AND sr.status = 'reserve'
            ORDER BY sr.aangemaakt_op ASC
            LIMIT 1
        ", [
            'shift_id' => $shiftId,
        ]);

        return $row ? $this->map($row) : null;
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findWaiting(int $shiftId): array
    {
        return $this->findByShiftAndStatus($shiftId, ShiftRegistration::STATUS_WACHTEND);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findReserve(int $shiftId): array
    {
        return $this->findByShiftAndStatus($shiftId, ShiftRegistration::STATUS_RESERVE);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findConfirmed(int $shiftId): array
    {
        return $this->findByShiftAndStatus($shiftId, ShiftRegistration::STATUS_BEVESTIGD);
    }

    /**
     * @return ShiftRegistration[]
     */
    private function findByShiftAndStatus(
        int $shiftId,
        string $status
    ): array {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT
                    sr.*,
                    l.voornaam AS lid_voornaam,
                    l.achternaam AS lid_achternaam,
                    l.email AS lid_email
                FROM shift_registrations sr
                INNER JOIN leden l
                    ON l.lid_id = sr.lid_id
                WHERE sr.shift_id = :shift_id
                  AND sr.status = :status
                ORDER BY sr.aangemaakt_op ASC
            ", [
                'shift_id' => $shiftId,
                'status' => $status,
            ])
        );
    }
}