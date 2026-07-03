<?php

declare(strict_types=1);

namespace AEFS\Http\Requests;

final class MemberRequest
{
    public function __construct(
        private array $input
    ) {
    }

    public function all(): array
    {
        return $this->sanitize($this->input);
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {

            if (is_string($value)) {

                $data[$key] = trim($value);

            }

        }

        $data['voornaam'] = $data['voornaam'] ?? '';

        $data['achternaam'] = $data['achternaam'] ?? '';

        $data['email'] = strtolower(
            trim($data['email'] ?? '')
        );

        $data['telefoon'] = $data['telefoon'] ?? '';

        $data['straat'] = $data['straat'] ?? '';

        $data['postcode'] = $data['postcode'] ?? '';

        $data['gemeente'] = $data['gemeente'] ?? '';

        $data['land'] = trim(
            $data['land'] ?? 'België'
        );

        $data['geslacht'] = $data['geslacht'] ?? '';

        $data['geboortedatum'] = $data['geboortedatum'] ?: null;

        $data['rekeningnummer'] = strtoupper(
            str_replace(
                ' ',
                '',
                $data['rekeningnummer'] ?? ''
            )
        );

        $data['rijksregisternummer'] = str_replace(
            [' ', '.', '-'],
            '',
            $data['rijksregisternummer'] ?? ''
        );

        $data['tshirtmaat'] = $data['tshirtmaat'] ?? '';

        $data['opmerkingen'] = trim(
            $data['opmerkingen'] ?? ''
        );

        $data['actief'] = isset($data['actief']);

        $data['gdpr_consent'] = isset($data['gdpr_consent']);

        return $data;
    }
}