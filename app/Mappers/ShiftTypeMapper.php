<?php

namespace App\Mappers;

use App\Models\ShiftType;

final class ShiftTypeMapper
{
    public function map(array $row): ShiftType
    {
        return new ShiftType(
            id: isset($row['id']) ? (int) $row['id'] : null,
            naam: (string) $row['naam'],
            actief: (bool) $row['actief'],
            aangemaaktOp: $row['aangemaakt_op'] ?? null,
            bijgewerktOp: $row['bijgewerkt_op'] ?? null,
        );
    }
}