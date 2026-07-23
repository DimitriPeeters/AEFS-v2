<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var \AEFS\Models\Event $event */
/** @var array $errors */



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => $event->titel,

    'subtitle' => 'Evenement wijzigen',

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors),

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/events/' . $event->eventId) ?>"
>

    <?= csrf_field() ?>

    <?= method_field('PUT') ?>

    <?= $this->component('events/form', ['event' => $event]) ?>

</form>
<?php $this->endSection(); ?>
