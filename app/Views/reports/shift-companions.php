<?php

use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */

$this->extend('layouts.app', ['title' => $title ?? 'Samen op shift']);
$exportQuery = http_build_query([
    'event_id' => $selectedEventId,
    'shift_id' => $selectedShiftId ?: null,
]);
?>

<?php $this->startSection('content'); ?>
<div class="reports-page">
    <?= $this->component('page-header', [
        'title' => 'Samen op shift',
        'subtitle' => 'Gegroepeerde wensen van bevestigde deelnemers. Een wens is geen definitieve shifttoewijzing.',
    ]) ?>
    <section class="card">
        <header class="card__header"><h2 class="card__title">Selectie</h2></header>
        <div class="card__body">
            <form method="get" action="<?= $this->escape($helpers->url->to('/reports/shift-companions')) ?>" class="report-companion-form">
                <div class="form-group">
                    <label for="event_id" class="form-label">Evenement</label>
                    <select id="event_id" name="event_id" class="form-control" required>
                        <option value="">Kies een evenement</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event->eventId ?>" <?= $selectedEventId === $event->eventId ? 'selected' : '' ?>><?= $this->escape($event->titel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="shift_id" class="form-label">Shift (optioneel)</label>
                    <select id="shift_id" name="shift_id" class="form-control">
                        <option value="">Alle voorkeuren van het evenement</option>
                        <?php foreach ($shifts as $shift): ?>
                            <option value="<?= $shift->shiftId ?>" data-event-id="<?= $shift->eventId ?>" <?= $selectedShiftId === $shift->shiftId ? 'selected' : '' ?>><?= $this->escape(($shift->eventTitel ?? '') . ' · ' . $shift->displayNaam() . ' · ' . $shift->displayPeriode()) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tonen</button>
            </form>
        </div>
    </section>

    <?php if ($report !== null): ?>
        <section class="card">
            <header class="card__header">
                <div>
                    <h2 class="card__title"><?= $this->escape($report['event']->titel) ?></h2>
                    <?php if ($report['shift'] !== null): ?>
                        <p><?= $this->escape($report['shift']->displayNaam() . ' · ' . $report['shift']->displayPeriode()) ?></p>
                    <?php endif; ?>
                </div>
                <a class="btn btn-success" href="<?= $this->escape($helpers->url->to('/reports/shift-companions/export?' . $exportQuery)) ?>">Excel downloaden</a>
            </header>
            <div class="card__body">
                <?php if ($report['groups'] === []): ?>
                    <p>Er zijn nog geen actuele voorkeuren tussen bevestigde deelnemers.</p>
                <?php else: ?>
                    <?php foreach ($report['groups'] as $index => $group): ?>
                        <h3>Groep <?= $index + 1 ?></h3>
                        <div class="table-responsive">
                            <table class="table">
                                <thead><tr><th>Naam</th><th>Wil samen met</th></tr></thead>
                                <tbody>
                                <?php foreach ($group['members'] as $member): ?>
                                    <tr><td><?= $this->escape($member['achternaam'] . ' ' . $member['voornaam']) ?></td><td><?= $this->escape($member['wishes'] ?: '—') ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection('styles'); ?>
<style>
    .reports-page { display: grid; gap: 1.25rem; }
    .report-companion-form { display: flex; flex-wrap: wrap; align-items: end; gap: 1rem; }
    .report-companion-form .form-group { flex: 1 1 16rem; }
</style>
<?php $this->endSection(); ?>

<?php $this->startSection('scripts'); ?>
<script>
    const companionEvent = document.getElementById('event_id');
    const companionShift = document.getElementById('shift_id');
    if (companionEvent && companionShift) {
        const filterShifts = () => {
            for (const option of companionShift.options) {
                if (option.value !== '') {
                    option.hidden = option.dataset.eventId !== companionEvent.value;
                }
            }
            if (companionShift.selectedOptions[0]?.hidden) {
                companionShift.value = '';
            }
        };
        companionEvent.addEventListener('change', filterShifts);
        filterShifts();
    }
</script>
<?php $this->endSection(); ?>
