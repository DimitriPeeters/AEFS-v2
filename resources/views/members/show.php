<?php

declare(strict_types=1);

use AEFS\Core\Url;

/** @var AEFS\Models\Member $lid */

$title = 'Lidfiche';

ob_start();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

    <h1>

        <?= htmlspecialchars($lid->fullName(), ENT_QUOTES) ?>

    </h1>

    <div>

        <a
            class="btn"
            href="<?= Url::to('/leden') ?>"
        >

            ← Terug

        </a>

    </div>

</div>

<div class="grid grid-2">

    <div class="card">

        <h2>Persoonsgegevens</h2>

        <table class="table">

            <tr>
                <th width="220">Voornaam</th>
                <td><?= htmlspecialchars($lid->voornaam, ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Achternaam</th>
                <td><?= htmlspecialchars($lid->achternaam, ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Geboortedatum</th>
                <td><?= htmlspecialchars($lid->geboortedatum ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Geslacht</th>
                <td><?= htmlspecialchars($lid->geslacht ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>T-shirtmaat</th>
                <td><?= htmlspecialchars($lid->tshirtmaat ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Status</th>
                <td>

                    <?php if ($lid->isActive()): ?>

                        <span style="color:#16a34a;font-weight:bold;">

                            Actief

                        </span>

                    <?php else: ?>

                        <span style="color:#dc2626;font-weight:bold;">

                            Inactief

                        </span>

                    <?php endif; ?>

                </td>

            </tr>

        </table>

    </div>

    <div class="card">

        <h2>Contactgegevens</h2>

        <table class="table">

            <tr>
                <th width="220">E-mail</th>
                <td><?= htmlspecialchars($lid->email ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Telefoon</th>
                <td><?= htmlspecialchars($lid->telefoon ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>GSM</th>
                <td><?= htmlspecialchars($lid->gsm ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Straat</th>
                <td><?= htmlspecialchars($lid->straat ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Huisnummer</th>
                <td><?= htmlspecialchars($lid->huisnummer ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Bus</th>
                <td><?= htmlspecialchars($lid->bus ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Postcode</th>
                <td><?= htmlspecialchars($lid->postcode ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Gemeente</th>
                <td><?= htmlspecialchars($lid->gemeente ?? '', ENT_QUOTES) ?></td>
            </tr>

            <tr>
                <th>Land</th>
                <td><?= htmlspecialchars($lid->land ?? '', ENT_QUOTES) ?></td>
            </tr>

        </table>

    </div>

</div>

<br>

<div class="card">

    <h2>Extra informatie</h2>

    <table class="table">

        <tr>
            <th width="220">IBAN</th>
            <td><?= htmlspecialchars($lid->rekeningnummer ?? '', ENT_QUOTES) ?></td>
        </tr>

        <tr>
            <th>Rijksregisternummer</th>
            <td><?= htmlspecialchars($lid->rijksregisternummer ?? '', ENT_QUOTES) ?></td>
        </tr>

        <tr>
            <th>GDPR toestemming</th>
            <td><?= $lid->gdprConsent ? 'Ja' : 'Nee' ?></td>
        </tr>

        <tr>
            <th>GDPR datum</th>
            <td><?= htmlspecialchars($lid->gdprTimestamp ?? '', ENT_QUOTES) ?></td>
        </tr>

        <tr>
            <th>Aangemaakt op</th>
            <td><?= htmlspecialchars($lid->aangemaaktOp ?? '', ENT_QUOTES) ?></td>
        </tr>

        <tr>
            <th>Bijgewerkt op</th>
            <td><?= htmlspecialchars($lid->bijgewerktOp ?? '', ENT_QUOTES) ?></td>
        </tr>

    </table>

</div>

<?php if (!empty($lid->opmerkingen)): ?>

<br>

<div class="card">

    <h2>Opmerkingen</h2>

    <br>

    <?= nl2br(htmlspecialchars($lid->opmerkingen, ENT_QUOTES)) ?>

</div>

<?php endif; ?>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/app.php';