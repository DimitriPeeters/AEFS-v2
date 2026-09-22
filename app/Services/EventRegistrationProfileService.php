<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Support\BelgianDateTime;
use InvalidArgumentException;

final class EventRegistrationProfileService
{
    /** @var array<string, string> */
    private const REQUIRED_FIELDS = [
        'voornaam' => 'Voornaam',
        'achternaam' => 'Achternaam',
        'email' => 'E-mailadres',
        'telefoon' => 'Telefoonnummer',
        'geboortedatum' => 'Geboortedatum',
        'geslacht' => 'Geslacht',
        'straat' => 'Straat en huisnummer',
        'postcode' => 'Postcode',
        'gemeente' => 'Gemeente',
        'land' => 'Land',
    ];

    public function __construct(
        private readonly MemberService $members
    ) {
    }

    /** @return array<string, string> veld => label */
    public function missingFields(int $memberId): array
    {
        $member = $this->members->find($memberId);

        if ($member === null) {
            throw new InvalidArgumentException(
                'Aan dit account is geen geldig ledenprofiel gekoppeld.'
            );
        }

        $missing = [];

        foreach (self::REQUIRED_FIELDS as $field => $label) {
            if (trim((string) $member->{$field}) === '') {
                $missing[$field] = $label;
            }
        }

        return $missing;
    }

    /** @return array<string, string> */
    public function formValues(int $memberId): array
    {
        $member = $this->members->find($memberId);

        if ($member === null) {
            return [];
        }

        return [
            'voornaam' => $member->voornaam,
            'achternaam' => $member->achternaam,
            'email' => $member->email ?? '',
            'telefoon' => $member->telefoon ?? '',
            'geboortedatum' => BelgianDateTime::formatDate(
                $member->geboortedatum,
                ''
            ),
            'geslacht' => $member->geslacht ?? '',
            'straat' => $member->straat ?? '',
            'postcode' => $member->postcode ?? '',
            'gemeente' => $member->gemeente ?? '',
            'land' => $member->land ?? 'België',
        ];
    }

    /** @param array<string, mixed> $input */
    public function complete(int $memberId, array $input): void
    {
        $member = $this->members->find($memberId);

        if ($member === null) {
            throw new InvalidArgumentException(
                'Aan dit account is geen geldig ledenprofiel gekoppeld.'
            );
        }

        $data = (new MemberRequest(array_merge(
            $this->completeMemberData($member),
            array_intersect_key($input, self::REQUIRED_FIELDS)
        )))->all();

        foreach (self::REQUIRED_FIELDS as $field => $label) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                throw new InvalidArgumentException(
                    sprintf('%s is verplicht om je voor een evenement in te schrijven.', $label)
                );
            }
        }

        $this->members->update($memberId, $data);
    }

    /** @return array<string, mixed> */
    private function completeMemberData(Member $member): array
    {
        return [
            'voornaam' => $member->voornaam,
            'achternaam' => $member->achternaam,
            'email' => $member->email ?? '',
            'telefoon' => $member->telefoon ?? '',
            'straat' => $member->straat ?? '',
            'postcode' => $member->postcode ?? '',
            'gemeente' => $member->gemeente ?? '',
            'land' => $member->land ?? '',
            'geslacht' => $member->geslacht ?? '',
            'geboortedatum' => BelgianDateTime::formatDate(
                $member->geboortedatum,
                ''
            ),
            'rekeningnummer' => $member->rekeningnummer ?? '',
            'rijksregisternummer' => $member->rijksregisternummer ?? '',
            'tshirtmaat' => $member->tshirtmaat ?? '',
            'opmerkingen' => $member->opmerkingen ?? '',
            'actief' => $member->actief ? '1' : '0',
            'gdpr_consent' => $member->gdprConsent ? '1' : '0',
            'gdpr_timestamp' => $member->gdprTimestamp,
        ];
    }
}
