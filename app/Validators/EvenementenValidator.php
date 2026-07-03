<?php

declare(strict_types=1);

namespace AEFS\Validators;

use DateTime;
use InvalidArgumentException;

final class EvenementenValidator
{
    public function validate(
        array $data
    ): void {

        if (empty(trim($data['titel'] ?? ''))) {

            throw new InvalidArgumentException(
                'Titel is verplicht.'
            );

        }

        if (empty($data['startdatum'])) {

            throw new InvalidArgumentException(
                'Startdatum is verplicht.'
            );

        }

        if (empty($data['einddatum'])) {

            throw new InvalidArgumentException(
                'Einddatum is verplicht.'
            );

        }

        $start = DateTime::createFromFormat(
            'Y-m-d',
            $data['startdatum']
        );

        $einde = DateTime::createFromFormat(
            'Y-m-d',
            $data['einddatum']
        );

        if (!$start || !$einde) {

            throw new InvalidArgumentException(
                'Ongeldige datum.'
            );

        }

        if ($einde < $start) {

            throw new InvalidArgumentException(
                'De einddatum mag niet vóór de startdatum liggen.'
            );

        }

        if (
            !empty($data['max_deelnemers']) &&
            (int)$data['max_deelnemers'] < 1
        ) {

            throw new InvalidArgumentException(
                'Maximum deelnemers moet minstens 1 zijn.'
            );

        }

    }
}