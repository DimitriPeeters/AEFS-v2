<?php

declare(strict_types=1);

namespace AEFS\Models;

final class Member
{
    public function __construct(

        public readonly int $lidId,

        public readonly string $voornaam,

        public readonly string $achternaam,

        public readonly ?string $email,

        public readonly ?string $telefoon,

        public readonly ?string $gsm,

        public readonly ?string $straat,

        public readonly ?string $huisnummer,

        public readonly ?string $bus,

        public readonly ?string $postcode,

        public readonly ?string $gemeente,

        public readonly ?string $land,

        public readonly ?string $geslacht,

        public readonly ?string $geboortedatum,

        public readonly ?string $rekeningnummer,

        public readonly ?string $rijksregisternummer,

        public readonly ?string $tshirtmaat,

        public readonly bool $actief,

        public readonly bool $gdprConsent,

        public readonly ?string $gdprTimestamp,

        public readonly ?string $opmerkingen,

        public readonly ?string $aangemaaktOp,

        public readonly ?string $bijgewerktOp,

    ) {
    }

    public function fullName(): string
    {
        return trim($this->voornaam . ' ' . $this->achternaam);
    }

    public function isActive(): bool
    {
        return $this->actief;
    }
}