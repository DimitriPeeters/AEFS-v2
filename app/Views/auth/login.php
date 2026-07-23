<?php

use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */

$this->extend('layouts.guest', ['title' => 'Aanmelden']);
$email = (string) $helpers->old->get('email', '');
?>
<?php $this->startSection('content'); ?>
<div class="auth">
    <?= $this->component('card', ['class' => 'auth-card'], function () use ($helpers, $email): void { ?>
        <?php $this->startSlot('header'); ?>
            <div class="auth-card__header"><h1>AEFS</h1><p>Meld je aan bij AEFS Eventbeheer.</p></div>
        <?php $this->endSlot(); ?>
        <?= $helpers->form->open($helpers->url->to('/login'), 'POST', ['class' => 'form']) ?>
        <div class="form-group">
            <?= $helpers->form->label('email', 'E-mailadres') ?>
            <?= $helpers->form->email('email', $email, ['id' => 'email', 'required' => true, 'autofocus' => true]) ?>
            <?= $helpers->errorRenderer->field($helpers->errors, 'email') ?>
        </div>
        <div class="form-group">
            <?= $helpers->form->label('password', 'Wachtwoord') ?>
            <?= $helpers->form->password('password', ['id' => 'password', 'required' => true]) ?>
            <?= $helpers->errorRenderer->field($helpers->errors, 'password') ?>
        </div>
        <?= $helpers->form->button('Aanmelden', 'submit', ['class' => 'button button--primary button--block']) ?>
        <?= $helpers->form->close() ?>
        <div class="footer"><a href="<?= $this->escape($helpers->url->to('/forgot-password')) ?>">Wachtwoord vergeten?</a></div>
    <?php }) ?>
</div>
<?php $this->endSection(); ?>
