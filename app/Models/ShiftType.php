<?php

namespace App\Models;

final class ShiftType
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $naam,
        public readonly bool $actief = true,
        public readonly ?string $aangemaaktOp = null,
        public readonly ?string $bijgewerktOp = null,
    ) {
    }
}