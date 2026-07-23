<?php

$this->extend('layouts.base', [
    'title' => $title ?? 'AEFS Eventbeheer',
    'bodyClass' => 'app-layout',
]);
?>
<?php $this->startSection('body'); ?>
<div class="wrapper">
    <?= $this->partial('partials.sidebar') ?>
    <div class="main">
        <?= $this->partial('partials.header', ['title' => $title ?? 'AEFS Eventbeheer']) ?>
        <main class="content">
            <?= $this->partial('partials.flash') ?>
            <?= $this->partial('partials.errors') ?>
            <?= $this->section('content') ?>
        </main>
        <?= $this->partial('partials.footer') ?>
    </div>
</div>
<?php $this->endSection(); ?>
