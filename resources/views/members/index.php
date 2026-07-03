<?php

declare(strict_types=1);

use AEFS\Core\Url;

/** @var AEFS\Models\Member[] $leden */

$title = 'Leden';

ob_start();

?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

    <div>

        <h1>Leden</h1>

        <small>

            <?= count($leden) ?> leden gevonden

        </small>

    </div>

    <div>

        <a
            class="btn"
            href="<?= Url::to('/leden/nieuw') ?>"
        >

            + Nieuw lid

        </a>

    </div>

</div>

<div class="card">

    <form
        method="get"
        action="<?= Url::to('/leden') ?>"
    >

        <div
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
                placeholder="Zoek op naam, e-mail of gemeente..."
            >

            <button
                class="btn"
                type="submit"
            >

                Zoeken

            </button>

        </div>

    </form>

    <table class="table">

        <thead>

        <tr>

            <th width="80">ID</th>

            <th>Naam</th>

            <th>E-mail</th>

            <th>Gemeente</th>

            <th width="120">Status</th>

            <th width="220"></th>

        </tr>

        </thead>

        <tbody>

        <?php if ($leden === []) : ?>

            <tr>

                <td colspan="6" style="text-align:center;padding:50px;">

                    Geen leden gevonden.

                </td>

            </tr>

        <?php endif; ?>

        <?php foreach ($leden as $lid): ?>

            <tr>

                <td>

                    <?= $lid->lidId ?>

                </td>

                <td>

                    <strong>

                        <?= htmlspecialchars($lid->fullName(), ENT_QUOTES) ?>

                    </strong>

                </td>

                <td>

                    <?= htmlspecialchars($lid->email ?? '', ENT_QUOTES) ?>

                </td>

                <td>

                    <?= htmlspecialchars($lid->gemeente ?? '', ENT_QUOTES) ?>

                </td>

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

                <td>

                    <div
                        style="
                            display:flex;
                            gap:8px;
                            justify-content:flex-end;
                        "
                    >

                        <a
                            class="btn"
                            href="<?= Url::to('/leden/' . $lid->lidId) ?>"
                        >

                            Open

                        </a>

                        <a
                            class="btn"
                            href="<?= Url::to('/leden/' . $lid->lidId . '/bewerken') ?>"
                        >

                            Bewerken

                        </a>

                        <form
                            method="post"
                            action="<?= Url::to('/leden/' . $lid->lidId . '/verwijderen') ?>"
                            onsubmit="return confirm('Lid verwijderen?');"
                        >

                            <button
                                class="btn"
                                style="background:#dc2626;"
                            >

                                Verwijderen

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/app.php';