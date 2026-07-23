<?php

use AEFS\Core\View\Helper\ViewHelpers;
use App\Models\Event;

/** @var ViewHelpers $helpers */
/** @var Event $event */
/** @var string|null $title */

$this->extend(
    'layouts.app',
    [
        'title' => $title ?? 'Evenement wijzigen',
    ]
);
?>

<?php $this->startSection('content'); ?>
<div class="event-form-page">
    <?= $this->component(
        'page-header',
        [
            'title' => $event->titel,
            'subtitle' => 'Evenement wijzigen',
        ]
    ) ?>

    <form
        method="post"
        action="<?= $this->escape(
            $helpers->url->to(
                '/events/' . $event->eventId . '/update'
            )
        ) ?>"
        novalidate
    >
        <?= $helpers->csrf->field() ?>

        <?= $this->component(
            'events/form',
            [
                'event' => $event,
            ]
        ) ?>
    </form>
</div>
<?php $this->endSection(); ?>

<?php $this->startSection('styles'); ?>
<style>
    .event-form-page {
        display: grid;
        gap: 1.25rem;
    }
</style>
<?php $this->endSection(); ?>
