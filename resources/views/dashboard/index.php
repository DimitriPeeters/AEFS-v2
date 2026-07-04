<?php

declare(strict_types=1);

?>

<h1>Dashboard</h1>
<p>Welkom in AEFS v2</p>

<div class="dashboard-grid mb-4">
    <div class="card">
        <h3>Leden</h3>
        <p><?= (int) $statistics['members'] ?></p>
    </div>

    <div class="card">
        <h3>Gebruikers</h3>
        <p><?= (int) $statistics['users'] ?></p>
    </div>

    <div class="card">
        <h3>Evenementen</h3>
        <p><?= (int) $statistics['events'] ?></p>
    </div>

    <div class="card">
        <h3>Open Shifts</h3>
        <p><?= count($openShifts) ?></p>
    </div>
</div>

<h2>Laatste leden</h2>

<table class="table">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Gemeente</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($latestMembers)): ?>
            <tr>
                <td colspan="2">Geen leden gevonden.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Komende evenementen</h2>

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
                <td colspan="2">Geen evenementen gepland.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Openstaande shifts</h2>

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
                <td colspan="4">Geen openstaande shifts.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>