<?php

declare(strict_types=1);

/** @var \AEFS\Models\Event $event */

$title = 'Evenement bewerken';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h1>Evenement bewerken</h1>

        <a href="/events" class="btn btn-outline-secondary">
            Terug
        </a>

    </div>

    <form method="post" action="/events/<?= $event->eventId ?>/update">

        <?php require __DIR__ . '/form.php'; ?>

    </form>

</div>