<?php



use AEFS\Core\View\Helper\ViewHelpers;

/** @var AEFS\Models\Event[] $events */
/** @var string $zoekterm */

$zoekterm ??= '';



$this->extend('layouts.app', ['title' => $title ?? null]);
?>
<?php $this->startSection('content'); ?>

<?= $this->component('page-header', [

    'title' => 'Evenementen',

    'subtitle' => 'Overzicht van alle evenementen',

    'actions' => $this->component('button', [

        'text' => 'Nieuw evenement',

        'icon' => 'plus',

        'type' => 'primary',

        'href' => $helpers->url->to('/events/create'),

    ]),

]) ?>

<?= $this->component('card', [

    'content' => $this->component('search-box', [

        'action' => $helpers->url->to('/events'),

        'name' => 'q',

        'value' => $zoekterm,

        'placeholder' => 'Zoek op titel, locatie of omschrijving...',

    ]),

]) ?>

<br>

<?= $this->component('card', [

    'content' => $this->component('events/table', [

        'events' => $events,

    ]),

]) ?>
<?php $this->endSection(); ?>
