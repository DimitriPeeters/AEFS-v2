<?php

declare(strict_types=1);

/** @var \AEFS\Models\Event $event */

$title = 'Evenement';

?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1><?= htmlspecialchars($event->titel) ?></h1>

        <div>

            <a href="/events/<?= $event->eventId ?>/edit" class="btn btn-warning">
                Bewerken
            </a>

            <a href="/events" class="btn btn-secondary">
                Terug
            </a>

        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <table class="table table-borderless mb-0">

                <tr>
                    <th width="220">Titel</th>
                    <td><?= htmlspecialchars($event->titel) ?></td>
                </tr>

                <tr>
                    <th>Beschrijving</th>
                    <td><?= nl2br(htmlspecialchars($event->beschrijving ?? '')) ?></td>
                </tr>

                <tr>
                    <th>Locatie</th>
                    <td><?= htmlspecialchars($event->locatie ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Startdatum</th>
                    <td><?= htmlspecialchars($event->startdatum) ?></td>
                </tr>

                <tr>
                    <th>Einddatum</th>
                    <td><?= htmlspecialchars($event->einddatum ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Maximum deelnemers</th>
                    <td><?= htmlspecialchars((string)($event->maxDeelnemers ?? '-')) ?></td>
                </tr>

            </table>

        </div>

    </div>

</div>