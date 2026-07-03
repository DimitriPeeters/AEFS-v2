<?php

declare(strict_types=1);

use AEFS\Core\Url;

$title = 'Nieuwe gebruiker';

$gebruiker = null;

ob_start();

?>

<div class="card">

    <h1>Nieuwe gebruiker</h1>

    <br>

    <form
        method="post"
        action="<?= Url::to('/gebruikers') ?>"
    >

        <?php require __DIR__ . '/form.php'; ?>

    </form>

</div>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/../layouts/app.php';