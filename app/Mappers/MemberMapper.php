<?php

declare(strict_types=1);

namespace AEFS\Mappers;

use AEFS\Models\Member;
use AEFS\Services\EncryptionService;

final class MemberMapper
{
    public function __construct(
        private EncryptionService $encryption
    ) {
    }

    public function fromDatabase(array $row): Member
    {
        return new Member(

            lidId: (int) $row['lid_id'],

            voornaam: (string) $row['voornaam'],

            achternaam: (string) $row['achternaam'],

            email: $row['email'],

            telefoon: $row['telefoon'],

            straat: $row['straat'],

            postcode: $row['postcode'],

            gemeente: $row['gemeente'],

            land: $row['land'],

            geslacht: $row['geslacht'],

            geboortedatum: $row['geboortedatum'],

            rekeningnummer: $this->encryption->decrypt(
                $row['rekeningnummer']
            ),

            rijksregisternummer: $this->encryption->decrypt(
                $row['rijksregisternummer']
            ),

            tshirtmaat: $row['tshirtmaat'],

            actief: (bool) $row['actief'],

            gdprConsent: (bool) $row['gdpr_consent'],

            gdprTimestamp: $row['gdpr_timestamp'],

            opmerkingen: $row['opmerkingen'],

            aangemaaktOp: $row['aangemaakt_op'],

            bijgewerktOp: $row['bijgewerkt_op']

        );
    }

    public function toDatabase(array $data): array
    {
        return [

            'voornaam' => trim((string)($data['voornaam'] ?? '')),

            'achternaam' => trim((string)($data['achternaam'] ?? '')),

            'email' => trim((string)($data['email'] ?? '')),

            'telefoon' => trim((string)($data['telefoon'] ?? '')),

            'straat' => trim((string)($data['straat'] ?? '')),

            'postcode' => trim((string)($data['postcode'] ?? '')),

            'gemeente' => trim((string)($data['gemeente'] ?? '')),

            'land' => trim((string)($data['land'] ?? '')),

            'geslacht' => trim((string)($data['geslacht'] ?? '')),

            'geboortedatum' => $data['geboortedatum'] ?: null,

            'rekeningnummer' => $this->encryption->encrypt(
                $data['rekeningnummer'] ?? null
            ),

            'rijksregisternummer' => $this->encryption->encrypt(
                $data['rijksregisternummer'] ?? null
            ),

            'tshirtmaat' => trim((string)($data['tshirtmaat'] ?? '')),

            'opmerkingen' => trim((string)($data['opmerkingen'] ?? '')),

            'actief' => isset($data['actief']) ? 1 : 0,

            'gdpr_consent' => isset($data['gdpr_consent']) ? 1 : 0,

            'gdpr_timestamp' => isset($data['gdpr_consent'])
                ? date('Y-m-d H:i:s')
                : null,

        ];
    }
}