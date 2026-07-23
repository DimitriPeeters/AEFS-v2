<?php

/** @var array<string, int> $statistics */
/** @var list<array<string, mixed>> $latestMembers */
/** @var list<array<string, mixed>> $upcomingEvents */
/** @var list<array<string, mixed>> $openShifts */

$this->extend('layouts.app', [
    'title' => 'Dashboard',
]);
?>

<?php $this->startSection('content'); ?>

<div class="dashboard">
    <div class="dashboard__header">
        <div>
            <h2 class="dashboard__title">
                Dashboard
            </h2>

            <p class="dashboard__description">
                Welkom in AEFS Eventbeheer.
            </p>
        </div>
    </div>

    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-card__label">
                Leden
            </div>

            <div class="stat-card__value">
                <?= $this->escape(
                    $statistics['members'] ?? 0
                ) ?>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__label">
                Gebruikers
            </div>

            <div class="stat-card__value">
                <?= $this->escape(
                    $statistics['users'] ?? 0
                ) ?>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__label">
                Evenementen
            </div>

            <div class="stat-card__value">
                <?= $this->escape(
                    $statistics['events'] ?? 0
                ) ?>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-card__label">
                Open shifts
            </div>

            <div class="stat-card__value">
                <?= $this->escape(
                    $statistics['shifts'] ?? 0
                ) ?>
            </div>
        </article>
    </div>

    <section class="dashboard-section">
        <div class="dashboard-section__header">
            <h3 class="dashboard-section__title">
                Laatste leden
            </h3>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Gemeente</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($latestMembers === []): ?>
                        <tr>
                            <td colspan="2">
                                Geen leden gevonden.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($latestMembers as $member): ?>
                            <tr>
                                <td>
                                    <?= $this->escape(
                                        trim(
                                            (string) ($member['voornaam'] ?? '')
                                            . ' '
                                            . (string) ($member['achternaam'] ?? '')
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <?= $this->escape(
                                        $member['gemeente'] ?? '-'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section__header">
            <h3 class="dashboard-section__title">
                Komende evenementen
            </h3>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Evenement</th>
                        <th>Datum</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($upcomingEvents === []): ?>
                        <tr>
                            <td colspan="2">
                                Geen evenementen gepland.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $event): ?>
                            <tr>
                                <td>
                                    <?= $this->escape(
                                        $event['titel'] ?? '-'
                                    ) ?>
                                </td>

                                <td>
                                    <?= $this->escape(
                                        $event['startdatum'] ?? '-'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section__header">
            <h3 class="dashboard-section__title">
                Openstaande shifts
            </h3>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Shift</th>
                        <th>Datum</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($openShifts === []): ?>
                        <tr>
                            <td colspan="2">
                                Geen openstaande shifts.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($openShifts as $shift): ?>
                            <tr>
                                <td>
                                    <?= $this->escape(
                                        $shift['naam'] ?? '-'
                                    ) ?>
                                </td>

                                <td>
                                    <?= $this->escape(
                                        $shift['shift_datum'] ?? '-'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php $this->endSection(); ?>