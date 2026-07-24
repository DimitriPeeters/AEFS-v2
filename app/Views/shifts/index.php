<?php

use AEFS\Core\View\Helper\ViewHelpers;
use App\Models\Event;
use App\Models\Shift;
use App\Models\ShiftRegistration;

/** @var ViewHelpers $helpers */
/** @var bool|null $isAdmin */
/** @var Event[] $events */
/** @var Shift[] $shifts */
/** @var ShiftRegistration[] $memberRegistrations */
/** @var ShiftRegistration[] $pendingRegistrations */
/** @var string|null $title */

$isAdmin ??= false;
$events ??= [];
$shifts ??= [];
$memberRegistrations ??= [];
$pendingRegistrations ??= [];

$this->extend(
    'layouts.app',
    [
        'title' => $title ?? 'Shiftplanning',
    ]
);

$registrationsByShift = [];

foreach ($memberRegistrations as $registration) {
    $registrationsByShift[$registration->shiftId] = $registration;
}

$shiftCountByEvent = [];

foreach ($shifts as $shift) {
    $shiftCountByEvent[$shift->eventId] = (
        $shiftCountByEvent[$shift->eventId] ?? 0
    ) + 1;
}

$activeShifts = count(
    array_filter(
        $shifts,
        static fn(Shift $shift): bool => $shift->isActief()
    )
);

$confirmedTotal = array_sum(
    array_map(
        static fn(Shift $shift): int => $shift->aantalBevestigd,
        $shifts
    )
);

$reserveTotal = array_sum(
    array_map(
        static fn(Shift $shift): int => $shift->aantalReserve,
        $shifts
    )
);

$actions = '';

if ($isAdmin) {
    $actions = sprintf(
        '<a href="%s" class="btn btn-primary">Nieuwe shift</a>',
        $this->escape($helpers->url->to('/shifts/create'))
    );
}
?>

<?php $this->startSection('content'); ?>
<div class="shift-index-page">
    <?= $this->component(
        'page-header',
        [
            'title' => 'Shiftplanning',
            'subtitle' => $isAdmin
                ? 'Beheer shifts, bezetting en inschrijvingsbeslissingen.'
                : 'Kies je shifts en volg de status van je inschrijvingen.',
            'actions' => $actions,
        ]
    ) ?>

    <?php if ($isAdmin): ?>
        <div class="shift-summary-grid">
            <div class="shift-summary-card">
                <span>Actieve shifts</span>
                <strong><?= $activeShifts ?></strong>
            </div>

            <div class="shift-summary-card">
                <span>Wachtende aanvragen</span>
                <strong><?= count($pendingRegistrations) ?></strong>
            </div>

            <div class="shift-summary-card">
                <span>Bevestigde plaatsen</span>
                <strong><?= $confirmedTotal ?></strong>
            </div>

            <div class="shift-summary-card">
                <span>Reserve</span>
                <strong><?= $reserveTotal ?></strong>
            </div>
        </div>

        <section class="card">
            <header class="card__header shift-card-header">
                <div>
                    <h2 class="card__title">Evenementplanningen</h2>
                    <p>Kies een evenement om de volledige planning te beheren.</p>
                </div>
            </header>

            <div class="card__body">
                <?php if ($events === []): ?>
                    <?= $this->component(
                        'empty-state',
                        [
                            'title' => 'Geen evenementen beschikbaar',
                            'text' => 'Maak eerst een evenement aan.',
                        ]
                    ) ?>
                <?php else: ?>
                    <div class="shift-event-grid">
                        <?php foreach ($events as $event): ?>
                            <a
                                href="<?= $this->escape(
                                    $helpers->url->to(
                                        '/shifts/event/' . $event->eventId
                                    )
                                ) ?>"
                                class="shift-event-card"
                            >
                                <span class="shift-event-card__status badge <?= $this->escape($event->statusCssClass()) ?>">
                                    <?= $this->escape($event->statusLabel()) ?>
                                </span>

                                <strong><?= $this->escape($event->titel) ?></strong>
                                <span><?= $this->escape($event->displayDate()) ?></span>
                                <small>
                                    <?= $shiftCountByEvent[$event->eventId] ?? 0 ?> shift(s)
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($pendingRegistrations !== []): ?>
            <section class="card">
                <header class="card__header">
                    <h2 class="card__title">Wachtende shiftinschrijvingen</h2>
                </header>

                <div class="card__body shift-table-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Vrijwilliger</th>
                                    <th>Evenement</th>
                                    <th>Shift</th>
                                    <th>Aangevraagd</th>
                                    <th>Actie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRegistrations as $registration): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $this->escape($registration->lidNaam()) ?></strong>
                                            <small class="shift-cell-muted">
                                                <?= $this->escape($registration->lidEmail ?? '-') ?>
                                            </small>
                                        </td>
                                        <td><?= $this->escape($registration->eventTitel ?? '-') ?></td>
                                        <td><?= $this->escape($registration->displayShiftPeriode()) ?></td>
                                        <td><?= $this->escape($registration->displayAangemaaktOp()) ?></td>
                                        <td>
                                            <a
                                                href="<?= $this->escape(
                                                    $helpers->url->to(
                                                        '/shifts/' . $registration->shiftId
                                                    )
                                                ) ?>"
                                                class="btn btn-secondary shift-small-button"
                                            >
                                                Beoordelen
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <section class="card">
        <header class="card__header shift-card-header">
            <div>
                <h2 class="card__title">
                    <?= $isAdmin ? 'Alle shifts' : 'Beschikbare shifts' ?>
                </h2>
                <p>
                    <?= $isAdmin
                        ? 'Overzicht van alle ingeplande functies.'
                        : 'Elke aanvraag wordt eerst door een administrator beoordeeld.' ?>
                </p>
            </div>
        </header>

        <div class="card__body shift-table-body">
            <?php if ($shifts === []): ?>
                <?= $this->component(
                    'empty-state',
                    [
                        'title' => 'Geen shifts gevonden',
                        'text' => $isAdmin
                            ? 'Maak een eerste shift aan voor een evenement.'
                            : 'Er staan momenteel geen shifts open voor inschrijving.',
                    ]
                ) ?>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table shift-table">
                        <thead>
                            <tr>
                                <th>Evenement en functie</th>
                                <th>Datum en tijd</th>
                                <th>Bezetting</th>
                                <th>Status</th>
                                <th>Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shifts as $shift): ?>
                                <?php
                                $memberRegistration = $registrationsByShift[$shift->shiftId]
                                    ?? null;
                                ?>
                                <tr>
                                    <td>
                                        <span
                                            class="shift-type-dot"
                                            style="--shift-color: <?= $this->escape(
                                                $shift->typeKleur ?? '#1E3A8A'
                                            ) ?>"
                                            aria-hidden="true"
                                        ></span>
                                        <strong><?= $this->escape($shift->displayNaam()) ?></strong>
                                        <small class="shift-cell-muted">
                                            <?= $this->escape($shift->eventTitel ?? '-') ?>
                                        </small>
                                    </td>
                                    <td><?= $this->escape($shift->displayPeriode()) ?></td>
                                    <td>
                                        <strong>
                                            <?= $shift->aantalBevestigd ?> / <?= $shift->maxPersonen ?>
                                        </strong>
                                        <small class="shift-cell-muted">
                                            <?= $shift->beschikbarePlaatsen() ?> plaats(en) vrij
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($memberRegistration !== null && !$isAdmin): ?>
                                            <span class="badge <?= $this->escape($memberRegistration->statusCssClass()) ?>">
                                                <?= $this->escape($memberRegistration->statusLabel()) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge <?= $this->escape($shift->statusCssClass()) ?>">
                                                <?= $this->escape($shift->statusLabel()) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a
                                            href="<?= $this->escape(
                                                $helpers->url->to('/shifts/' . $shift->shiftId)
                                            ) ?>"
                                            class="btn btn-secondary shift-small-button"
                                        >
                                            <?= $isAdmin ? 'Beheren' : 'Bekijken' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection('styles'); ?>
<style>
    .shift-index-page {
        display: grid;
        gap: 1.25rem;
    }

    .shift-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .shift-summary-card {
        display: grid;
        gap: 0.4rem;
        padding: 1.15rem;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-large);
        box-shadow: var(--shadow-card);
    }

    .shift-summary-card span {
        color: var(--color-text-muted);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }

    .shift-summary-card strong {
        font-size: 1.7rem;
    }

    .shift-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .shift-card-header p {
        margin: 0.4rem 0 0;
        color: var(--color-text-muted);
    }

    .shift-event-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .shift-event-card {
        position: relative;
        display: grid;
        gap: 0.35rem;
        min-height: 145px;
        padding: 1.1rem;
        color: var(--color-text);
        text-decoration: none;
        background: #f8fafc;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-medium);
        transition: border-color 0.15s ease, transform 0.15s ease;
    }

    .shift-event-card:hover {
        color: var(--color-text);
        border-color: var(--color-primary);
        text-decoration: none;
        transform: translateY(-2px);
    }

    .shift-event-card__status {
        width: max-content;
        margin-bottom: 0.25rem;
    }

    .shift-event-card strong {
        font-size: 1rem;
    }

    .shift-event-card span:not(.badge),
    .shift-event-card small,
    .shift-cell-muted {
        display: block;
        color: var(--color-text-muted);
        font-size: 0.82rem;
        line-height: 1.4;
    }

    .shift-table-body {
        padding-top: 0;
    }

    .shift-table td:first-child {
        min-width: 230px;
    }

    .shift-type-dot {
        display: inline-block;
        width: 0.7rem;
        height: 0.7rem;
        margin-right: 0.45rem;
        background: var(--shift-color);
        border-radius: 50%;
    }

    .shift-small-button {
        min-height: 34px;
        padding: 0.45rem 0.7rem;
        font-size: 0.82rem;
    }

    @media (max-width: 1100px) {
        .shift-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .shift-event-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 680px) {
        .shift-summary-grid,
        .shift-event-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php $this->endSection(); ?>
