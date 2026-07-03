<?php

declare(strict_types=1);

use AEFS\Core\Url;

?>

<?= component('page-header', [

    'title' => 'Dashboard',

    'subtitle' => 'Welkom in AEFS v2',

    'actions' => component('button', [

        'text' => 'Nieuw evenement',

        'type' => 'primary',

        'href' => Url::to('/events/create')

    ])

]) ?>

<div class="dashboard-grid mb-4">

<?= component('stat-card', [

    'title' => 'Leden',

    'value' => $statistics['members'],

    'icon' => icon('users'),

    'color' => 'primary'

]) ?>

<?= component('stat-card', [

    'title' => 'Gebruikers',

    'value' => $statistics['users'],

    'icon' => icon('user'),

    'color' => 'success'

]) ?>

<?= component('stat-card', [

    'title' => 'Evenementen',

    'value' => $statistics['events'],

    'icon' => icon('calendar'),

    'color' => 'warning'

]) ?>

<?= component('stat-card', [

    'title' => 'Open Shifts',

    'value' => count($openShifts),

    'icon' => icon('clock'),

    'color' => 'danger'

]) ?>

</div>

<div class="dashboard-columns">

    <div>

        <?= component('card', [

            'title' => 'Laatste leden',

            'content' => ''

        ]) ?>

        <table class="table">

            <thead>

                <tr>

                    <th>Naam</th>

                    <th>Gemeente</th>

                    <th></th>

                </tr>

            </thead>

            <tbody>

            <?php if (empty($latestMembers)): ?>

                <tr>

                    <td colspan="3">

                        Geen leden gevonden.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($latestMembers as $member): ?>

                    <tr>

                        <td>

                            <?= htmlspecialchars(
                                $member['voornaam'] . ' ' . $member['achternaam']
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $member['gemeente'] ?? '-'
                            ) ?>

                        </td>

                        <td>

                            <a
                                href="<?= Url::to('/members/' . $member['lid_id']) ?>"
                                class="btn btn-sm btn-primary"
                            >

                                Open

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <div>

        <?= component('card', [

            'title' => 'Komende evenementen',

            'content' => ''

        ]) ?>

        <table class="table">

            <thead>

                <tr>

                    <th>Evenement</th>

                    <th>Datum</th>

                </tr>

            </thead>

            <tbody>

            <?php if (empty($upcomingEvents)): ?>

                <tr>

                    <td colspan="2">

                        Geen evenementen gepland.

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($upcomingEvents as $event): ?>

                    <tr>

                        <td>

                            <?= htmlspecialchars($event['titel']) ?>

                        </td>

                        <td>

                            <?= date(
                                'd/m/Y',
                                strtotime($event['startdatum'])
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<div class="mt-4">

    <?= component('card', [

        'title' => 'Openstaande shifts',

        'content' => ''

    ]) ?>

    <table class="table">

        <thead>

            <tr>

                <th>Evenement</th>

                <th>Shift</th>

                <th>Datum</th>

                <th>Bezetting</th>

            </tr>

        </thead>

        <tbody>

        <?php if (empty($openShifts)): ?>

            <tr>

                <td colspan="4">

                    Geen openstaande shifts.

                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($openShifts as $shift): ?>

                <tr>

                    <td>

                        <?= htmlspecialchars($shift['event_titel']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($shift['naam']) ?>

                    </td>

                    <td>

                        <?= date(
                            'd/m/Y',
                            strtotime($shift['shift_datum'])
                        ) ?>

                    </td>

                    <td>

                        <?= $shift['ingevuld'] ?>

                        /

                        <?= $shift['max_personen'] ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>