<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var array $errors */

$title = 'Nieuw evenement';



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Nieuw evenement',

    'subtitle' => 'Evenement aanmaken',

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors),

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/events') ?>"
>

    <?= csrf_field() ?>

    <?= $this->component('events/form', ['event' => $event ?? null]) ?>

</form>
<?php $this->endSection(); ?>
