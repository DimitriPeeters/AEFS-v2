<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var \AEFS\Models\Member $lid */
/** @var array $errors */



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => $lid->fullName(),

    'subtitle' => 'Lid wijzigen'

]) ?>

<?php if (!empty($errors)): ?>

    <?= $this->component('alert', [

        'type' => 'danger',

        'message' => implode('<br>', $errors)

    ]) ?>

<?php endif; ?>

<form
    method="post"
    action="<?= $helpers->url->to('/members/' . $lid->lidId) ?>"
>

    <?= csrf_field() ?>

    <?= method_field('PUT') ?>

    <?= $this->component('card', [

        'title' => 'Lidgegevens',

        'content' => $this->component('members/form', [

            'lid' => $lid

        ])

    ]) ?>

</form>
<?php $this->endSection(); ?>
