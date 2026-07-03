<?php

declare(strict_types=1);

use AEFS\Core\Url;

/** @var AEFS\Models\User $gebruiker */

$title = 'Gebruiker bewerken';

ob_start();

?>

<div class="card">

    <h1>Gebruiker bewerken</h1>

    <br>

    <form
        method="post"
        action="<?= Url::to('/gebruikers/' . $gebruiker->gebruikerId) ?>"
    >

        <?php require __DIR__ . '/form.php'; ?>

    </form>

</div>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/../layouts/app.php';