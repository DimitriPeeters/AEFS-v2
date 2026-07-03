<?php

declare(strict_types=1);

$title = 'Dashboard';

ob_start();
?>

<div class="grid grid-4">

    <div class="card stat-card">

        <div class="stat-title">
            Leden
        </div>

        <div class="stat-value">
            0
        </div>

        <div class="stat-footer">
            Totaal aantal leden
        </div>

    </div>

    <div class="card stat-card">

        <div class="stat-title">
            Evenementen
        </div>

        <div class="stat-value">
            0
        </div>

        <div class="stat-footer">
            Actieve evenementen
        </div>

    </div>

    <div class="card stat-card">

        <div class="stat-title">
            Open shiften
        </div>

        <div class="stat-value">
            0
        </div>

        <div class="stat-footer">
            Nog te bemannen
        </div>

    </div>

    <div class="card stat-card">

        <div class="stat-title">
            Inschrijvingen
        </div>

        <div class="stat-value">
            0
        </div>

        <div class="stat-footer">
            Openstaande inschrijvingen
        </div>

    </div>

</div>

<br>

<div class="grid grid-2">

    <div class="card">

        <h2>Welkom</h2>

        <br>

        <p>

            Welkom in <strong>AEFS v2</strong>.

        </p>

        <br>

        <p>

            Dit dashboard vormt de centrale startpagina van de applicatie.
            In de volgende stappen zullen hier automatisch statistieken,
            recente activiteiten, meldingen en snelkoppelingen verschijnen.

        </p>

    </div>

    <div class="card">

        <h2>Systeemstatus</h2>

        <br>

        <table class="table">

            <tr>

                <td>Authenticatie</td>

                <td>✅ OK</td>

            </tr>

            <tr>

                <td>Database</td>

                <td>✅ Verbonden</td>

            </tr>

            <tr>

                <td>Router</td>

                <td>✅ Actief</td>

            </tr>

            <tr>

                <td>Framework</td>

                <td>✅ Operationeel</td>

            </tr>

        </table>

    </div>

</div>

<br>

<div class="card">

    <h2>Volgende modules</h2>

    <br>

    <table class="table">

        <thead>

        <tr>

            <th>Module</th>

            <th>Status</th>

        </tr>

        </thead>

        <tbody>

        <tr>

            <td>Ledenbeheer</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        <tr>

            <td>Gebruikersbeheer</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        <tr>

            <td>Evenementen</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        <tr>

            <td>Shiftplanning</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        <tr>

            <td>Mailings</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        <tr>

            <td>Rapporten</td>

            <td>⏳ In ontwikkeling</td>

        </tr>

        </tbody>

    </table>

</div>

<style>

.stat-card{

    text-align:center;

}

.stat-title{

    color:#6b7280;

    font-size:15px;

    margin-bottom:15px;

}

.stat-value{

    font-size:42px;

    font-weight:bold;

    color:#2563eb;

    margin-bottom:10px;

}

.stat-footer{

    color:#9ca3af;

    font-size:13px;

}

</style>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/app.php';