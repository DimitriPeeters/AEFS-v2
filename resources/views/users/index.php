<?php

declare(strict_types=1);

use AEFS\Core\Url;

/** @var AEFS\Models\User[] $gebruikers */

$title = 'Gebruikers';

ob_start();

?>

<div class="page-header">

    <div>

        <h1>Gebruikers</h1>

        <small>

            <?= count($gebruikers) ?> gebruikers

        </small>

    </div>

</div>

<div class="card">

    <form
        method="get"
        action="<?= Url::to('/gebruikers') ?>"
        style="
            display:grid;
            grid-template-columns:1fr 180px;
            gap:15px;
            margin-bottom:25px;
        "
    >

        <input
            type="text"
            name="q"
            value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>"
            placeholder="Zoek gebruiker..."
        >

        <button
            class="btn"
            type="submit"
        >

            Zoeken

        </button>

    </form>

    <table class="table">

        <thead>

        <tr>

            <th>ID</th>

            <th>E-mail</th>

            <th>Rol</th>

            <th>Status</th>

            <th width="120"></th>

        </tr>

        </thead>

        <tbody>

        <?php if ($gebruikers === []) : ?>

            <tr>

                <td colspan="5" style="text-align:center;padding:40px;">

                    Geen gebruikers gevonden.

                </td>

            </tr>

        <?php endif; ?>

        <?php foreach ($gebruikers as $gebruiker): ?>

            <tr>

                <td>

                    <?= $gebruiker->gebruikerId ?>

                </td>

                <td>

                    <?= htmlspecialchars($gebruiker->email, ENT_QUOTES) ?>

                </td>

                <td>

                    <?= htmlspecialchars(ucfirst($gebruiker->rol), ENT_QUOTES) ?>

                </td>

                <td>

                    <?= $gebruiker->actief ? 'Actief' : 'Inactief' ?>

                </td>

                <td>

                    <a
                        class="btn"
                        href="<?= Url::to('/gebruikers/' . $gebruiker->gebruikerId) ?>"
                    >

                        Open

                    </a>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/app.php';