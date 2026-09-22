<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Shift;
use App\Support\BelgianDateTime;

final class EventRequest
{
    /**
     * @param array<string, mixed> $input
     */
    public function __construct(
        private readonly array $input,
        private readonly string $defaultShiftCompensation = Shift::DEFAULT_COMPENSATION,
        private readonly bool $defaultUsesGroups = false,
        private readonly string $defaultGroupSupplement = '10.00'
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

        $einddatum = BelgianDateTime::normalizeDateInput(
            $this->input['einddatum'] ?? ''
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
            'startdatum' => BelgianDateTime::normalizeDateInput(
                $this->input['startdatum'] ?? ''
            ),
            'einddatum' => $einddatum !== ''
                ? $einddatum
                : null,
            'status' => trim(
                (string) ($this->input['status'] ?? 'concept')
            ),
            'werkt_met_groepen' => array_key_exists(
                'werkt_met_groepen',
                $this->input
            )
                ? $this->isChecked('werkt_met_groepen')
                : $this->defaultUsesGroups,
            'groepstoeslag_bedrag' => $this->normalizeAmount(
                $this->input['groepstoeslag_bedrag']
                ?? $this->defaultGroupSupplement
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function shifts(): array
    {
        $rows = $this->input['shifts'] ?? [];

        if (!is_array($rows)) {
            return [];
        }

        $shifts = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !$this->hasShiftInput($row)) {
                continue;
            }

            $shifts[] = (new ShiftRequest(
                $row,
                $this->defaultShiftCompensation
            ))->all();
        }

        return $shifts;
    }

    /**
     * @return array{type: string, groep_id: int|null}
     */
    public function publicationAudience(): array
    {
        $groupId = (int) ($this->input['publicatie_groep_id'] ?? 0);

        return [
            'type' => trim(
                (string) (
                    $this->input['publicatie_doelgroep']
                    ?? 'alle_leden'
                )
            ),
            'groep_id' => $groupId > 0
                ? $groupId
                : null,
        ];
    }

    /** @return int[] */
    public function managerIds(): array
    {
        return $this->integerList($this->input['eventbeheerder_ids'] ?? []);
    }

    /** @return int[] */
    public function visibilityGroupIds(): array
    {
        return $this->integerList($this->input['zichtbare_groep_ids'] ?? []);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hasShiftInput(array $row): bool
    {
        foreach (
            [
                'type_id',
                'naam',
                'shift_datum',
                'starttijd',
                'eindtijd',
                'max_personen',
            ] as $key
        ) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function isChecked(string $key): bool
    {
        if (!array_key_exists($key, $this->input)) {
            return false;
        }

        return filter_var(
            $this->input[$key],
            FILTER_VALIDATE_BOOL
        );
    }

    private function normalizeAmount(mixed $value): string
    {
        $value = str_replace(
            ['€', ' '],
            '',
            trim((string) $value)
        );

        if (preg_match('/^\d+(?:[,.]\d{1,2})?$/', $value) !== 1) {
            return $value;
        }

        return number_format(
            (float) str_replace(',', '.', $value),
            2,
            '.',
            ''
        );
    }

    /** @return int[] */
    private function integerList(mixed $value): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $ids = array_values(array_unique(array_filter(
            array_map('intval', $value),
            static fn(int $id): bool => $id > 0
        )));
        sort($ids);

        return $ids;
    }
}
