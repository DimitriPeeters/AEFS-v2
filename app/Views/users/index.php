<?php



use AEFS\Core\View\Helper\ViewHelpers;

/**
 * @var AEFS\Models\User[] $gebruikers
 * @var string $zoekterm
 */

$zoekterm ??= '';



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Gebruikers',

    'subtitle' => 'Overzicht van alle gebruikers',

    'actions' => $this->component('button', [

        'text' => 'Nieuwe gebruiker',

        'icon' => 'plus',

        'type' => 'primary',

        'href' => $helpers->url->to('/users/create'),

    ]),

]) ?>

<?= $this->component('card', [

    'content' => $this->component('search-box', [

        'action' => $helpers->url->to('/users'),

        'value' => $zoekterm,

        'placeholder' => 'Zoek op naam of e-mailadres...',

    ]),

]) ?>

<br>

<?= $this->component('card', [

    'content' => $this->component('users/table', [

        'gebruikers' => $gebruikers,

    ]),

]) ?>
<?php $this->endSection(); ?>
