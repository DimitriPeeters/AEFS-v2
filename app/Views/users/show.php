<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var AEFS\Models\User $gebruiker */

$title = 'Gebruiker';




$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<div class="page-header">

    <div>

        <h1>Gebruiker</h1>

        <small>

            Detailfiche gebruiker #<?= $gebruiker->gebruikerId ?>

        </small>

    </div>

    <div>

        <a
            class="btn"
            href="<?= $helpers->url->to('/gebruikers') ?>"
        >

            ← Terug

        </a>

    </div>

</div>

<div class="grid grid-2">

    <div class="card">

        <h2>Account</h2>

        <br>

        <table class="table">

            <tr>

                <th width="220">ID</th>

                <td><?= $gebruiker->gebruikerId ?></td>

            </tr>

            <tr>

                <th>Lid ID</th>

                <td><?= $gebruiker->lidId ?></td>

            </tr>

            <tr>

                <th>E-mailadres</th>

                <td><?= htmlspecialchars($gebruiker->email, ENT_QUOTES) ?></td>

            </tr>

            <tr>

                <th>Rol</th>

                <td><?= htmlspecialchars(ucfirst($gebruiker->rol), ENT_QUOTES) ?></td>

            </tr>

            <tr>

                <th>Status</th>

                <td>

                    <?= $gebruiker->actief ? 'Actief' : 'Inactief' ?>

                </td>

            </tr>

        </table>

    </div>

    <div class="card">

        <h2>Beveiliging</h2>

        <br>

        <table class="table">

            <tr>

                <th width="220">Wachtwoord wijzigen</th>

                <td>

                    <?= $gebruiker->wachtwoordMoetWijzigen ? 'Ja' : 'Nee' ?>

                </td>

            </tr>

            <tr>

                <th>Mail blacklist</th>

                <td>

                    <?= $gebruiker->mailBlacklist ? 'Ja' : 'Nee' ?>

                </td>

            </tr>

            <tr>

                <th>Reset token</th>

                <td>

                    <?= $gebruiker->resetToken ?: '-' ?>

                </td>

            </tr>

            <tr>

                <th>Reset vervalt</th>

                <td>

                    <?= $gebruiker->resetTokenExpires ?: '-' ?>

                </td>

            </tr>

        </table>

    </div>

</div>

<br>

<div class="card">

    <div style="display:flex;gap:10px;">

        <a
            class="btn"
            href="<?= $helpers->url->to('/gebruikers/' . $gebruiker->gebruikerId . '/bewerken') ?>"
        >

            Bewerken

        </a>

        <form
            method="post"
            action="<?= $helpers->url->to('/gebruikers/' . $gebruiker->gebruikerId . '/verwijderen') ?>"
            onsubmit="return confirm('Gebruiker verwijderen?');"
        >

            <button
                type="submit"
                class="btn btn-danger"
            >

                Verwijderen

            </button>

        </form>

    </div>

</div>

<?php $this->endSection(); ?>
