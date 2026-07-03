
<?php

declare(strict_types=1);

use AEFS\Core\Url;

/** @var AEFS\Models\Member $lid */

$title = 'Lid bewerken';

ob_start();

?>

<div class="card">

    <h1>

        <?= htmlspecialchars($lid->fullName(), ENT_QUOTES) ?>

    </h1>

    <br>

    <form
        method="post"
        action="<?= Url::to('/leden/' . $lid->lidId) ?>"
    >

        <?php require __DIR__ . '/form.php'; ?>

    </form>

</div>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layouts/app.php';