<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var array $errors */



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Nieuw lid',

    'subtitle' => 'Lid toevoegen'

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors)

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/members') ?>"
>

    <?= csrf_field() ?>

    <?= $this->component('card', [

        'title' => 'Lidgegevens',

        'content' => $this->component('members/form')

    ]) ?>

</form>
<?php $this->endSection(); ?>
