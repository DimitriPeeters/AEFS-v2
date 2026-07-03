<?php

declare(strict_types=1);

namespace AEFS\Validators;

final class MemberValidator
{
    /**
     * @return array<string,string>
     */
    public function validate(array $data): array
    {
        $errors = [];

        $voornaam = trim((string)($data['voornaam'] ?? ''));
        $achternaam = trim((string)($data['achternaam'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $geboortedatum = trim((string)($data['geboortedatum'] ?? ''));

        if ($voornaam === '') {
            $errors['voornaam'] = 'Voornaam is verplicht.';
        }

        if ($achternaam === '') {
            $errors['achternaam'] = 'Achternaam is verplicht.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Het e-mailadres is ongeldig.';
        }

        if (
            $geboortedatum !== '' &&
            strtotime($geboortedatum) === false
        ) {
            $errors['geboortedatum'] = 'Ongeldige geboortedatum.';
        }

        if (
            !empty($data['rekeningnummer']) &&
            !preg_match('/^[A-Z]{2}[0-9A-Z ]+$/i', (string)$data['rekeningnummer'])
        ) {
            $errors['rekeningnummer'] = 'Ongeldig rekeningnummer.';
        }

        return $errors;
    }
}