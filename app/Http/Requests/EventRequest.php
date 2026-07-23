<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class EventRequest
{
    /**
     * @param array<string, mixed> $input
     */
    public function __construct(
        private readonly array $input
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $maxDeelnemers = trim(
            (string) ($this->input['max_deelnemers'] ?? '')
        );

        $einddatum = trim(
            (string) ($this->input['einddatum'] ?? '')
        );

        $beschrijving = trim(
            (string) ($this->input['beschrijving'] ?? '')
        );

        $locatie = trim(
            (string) ($this->input['locatie'] ?? '')
        );

        return [
            'titel' => trim(
                (string) ($this->input['titel'] ?? '')
            ),
            'beschrijving' => $beschrijving !== ''
                ? $beschrijving
                : null,
            'locatie' => $locatie !== ''
                ? $locatie
                : null,
            'max_deelnemers' => $maxDeelnemers !== ''
                ? (int) $maxDeelnemers
                : null,
            'startdatum' => trim(
                (string) ($this->input['startdatum'] ?? '')
            ),
            'einddatum' => $einddatum !== ''
                ? $einddatum
                : null,
            'status' => trim(
                (string) ($this->input['status'] ?? 'concept')
            ),
        ];
    }
}
