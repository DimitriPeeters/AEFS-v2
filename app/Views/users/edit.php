<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var AEFS\Models\User $gebruiker */
/** @var array $leden */
/** @var array $errors */



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => $gebruiker->fullName(),

    'subtitle' => 'Gebruiker wijzigen',

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors),

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/users/' . $gebruiker->gebruikerId) ?>"
>

    <?= csrf_field() ?>

    <?= method_field('PUT') ?>

    <?= $this->component('card', [

        'title' => 'Gebruikersgegevens',

        'content' => $this->component('users/form', [

            'gebruiker' => $gebruiker,

            'leden' => $leden,

        ]),

    ]) ?>

</form>
<?php $this->endSection(); ?>
