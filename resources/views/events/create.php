<?php

declare(strict_types=1);

$title = 'Nieuw evenement';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1>Nieuw evenement</h1>

        <a href="/events" class="btn btn-outline-secondary">
            Terug
        </a>

    </div>

    <form method="post" action="/events">

        <?php require __DIR__ . '/form.php'; ?>

    </form>

</div>