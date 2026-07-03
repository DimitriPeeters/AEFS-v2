<?php

declare(strict_types=1);

namespace AEFS\Mappers;

use AEFS\Models\User;

final class UserMapper
{
    public function fromDatabase(array $row): User
    {
        return new User(

            gebruikerId: (int) $row['gebruiker_id'],

            lidId: (int) $row['lid_id'],

            email: $row['email'],

            rol: $row['rol'],

            actief: (bool) $row['actief'],

            mailBlacklist: (bool) ($row['mail_blacklist'] ?? false),

            wachtwoordMoetWijzigen: (bool) ($row['wachtwoord_moet_wijzigen'] ?? false),

            passwordHash: $row['wachtwoord_hash'],

            resetToken: $row['reset_token'],

            resetTokenExpires: $row['reset_token_expires'],

            laatsteLogin: $row['laatste_login'],

            laatsteIp: $row['laatste_ip'],

            voornaam: $row['voornaam'],

            achternaam: $row['achternaam']

        );
    }

    public function toDatabase(array $data): array
    {
        return [

            'lid_id' => (int) $data['lid_id'],

            'email' => strtolower(
                trim($data['email'])
            ),

            'rol' => $data['rol'],

            'actief' => !empty($data['actief']) ? 1 : 0,

            'mail_blacklist' => !empty($data['mail_blacklist']) ? 1 : 0,

            'wachtwoord_moet_wijzigen' => !empty($data['wachtwoord_moet_wijzigen']) ? 1 : 0,

            'wachtwoord_hash' => $data['wachtwoord_hash'],

            'reset_token' => $data['reset_token'] ?? null,

            'reset_token_expires' => $data['reset_token_expires'] ?? null,

            'laatste_login' => $data['laatste_login'] ?? null,

            'laatste_ip' => $data['laatste_ip'] ?? null,

        ];
    }
}