<?php

declare(strict_types=1);

namespace AEFS\Mappers;

use AEFS\Models\ShiftRegistration;

final class ShiftRegistrationMapper
{
    public function map(array $row): ShiftRegistration
    {
        return new ShiftRegistration(
            id: isset($row['id']) ? (int) $row['id'] : null,
            shiftId: (int) $row['shift_id'],
            lidId: (int) $row['lid_id'],
            status: (string) $row['status'],
            aangemaaktOp: $row['aangemaakt_op'] ?? null,
            bijgewerktOp: $row['bijgewerkt_op'] ?? null,
            goedgekeurdDoor: isset($row['goedgekeurd_door']) ? (int) $row['goedgekeurd_door'] : null,
            goedgekeurdOp: $row['goedgekeurd_op'] ?? null,
            geannuleerdDoor: isset($row['geannuleerd_door']) ? (int) $row['geannuleerd_door'] : null,
            geannuleerdOp: $row['geannuleerd_op'] ?? null,
            annulatieReden: $row['annulatie_reden'] ?? null,
            lidVoornaam: $row['lid_voornaam'] ?? null,
            lidAchternaam: $row['lid_achternaam'] ?? null,
            lidEmail: $row['lid_email'] ?? null
        );
    }
}