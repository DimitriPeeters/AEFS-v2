<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var AEFS\Models\Member[] $leden */
/** @var string $zoekterm */

$zoekterm ??= '';



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Leden',

    'subtitle' => 'Overzicht van alle leden',

    'actions' => $this->component('button', [

        'text' => 'Nieuw lid',

        'icon' => 'plus',

        'type' => 'primary',

        'href' => $helpers->url->to('/members/create'),

    ]),

]) ?>

<?= $this->component('card', [

    'content' => $this->component('search-box', [

        'action' => $helpers->url->to('/members'),

        'value' => $zoekterm,

        'placeholder' => 'Zoek op naam, e-mail of gemeente...',

    ]),

]) ?>

<br>

<?= $this->component('card', [

    'content' => $this->component('members/table', [

        'leden' => $leden,

    ]),

]) ?>
<?php $this->endSection(); ?>
