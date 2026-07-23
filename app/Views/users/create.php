<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var array $leden */
/** @var array $errors */



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Nieuwe gebruiker',

    'subtitle' => 'Gebruiker toevoegen',

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors),

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/users') ?>"
>

    <?= csrf_field() ?>

    <?= $this->component('card', [

        'title' => 'Gebruikersgegevens',

        'content' => $this->component('users/form', [

            'leden' => $leden,

        ]),

    ]) ?>

</form>
<?php $this->endSection(); ?>
