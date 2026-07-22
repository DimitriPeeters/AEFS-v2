<?php


use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */

$this->extend('layouts.guest', [
    'title' => 'Aanmelden',
]);

$email = $helpers->old->get('email', '');
?>

<?php $this->startSection('content'); ?>

<div class="auth">
    <?= $this->component(
        'card',
        ['class' => 'auth-card'],
        function () use ($helpers, $email): void {
            $this->startSlot('header');
            ?>
            <div class="auth-card__header">
                <h1>Aanmelden</h1>

                <p>Meld je aan bij AEFS Eventbeheer.</p>
            </div>
            <?php
            $this->endSlot();

            echo $helpers->form->open(
                $helpers->url->to('/login'),
                'POST',
                [
                    'class' => 'form',
                    'autocomplete' => 'on',
                ]
            );

            ?>
            <div class="form-group">
                <?= $helpers->form->label(
                    'email',
                    'E-mailadres',
                    ['class' => 'form-label']
                ) ?>

                <?= $helpers->form->email(
                    'email',
                    $email,
                    [
                        'id' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                        'autocomplete' => 'email',
                        'autofocus' => true,
                    ]
                ) ?>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'email'
                ) ?>
            </div>

            <div class="form-group">
                <?= $helpers->form->label(
                    'password',
                    'Wachtwoord',
                    ['class' => 'form-label']
                ) ?>

                <?= $helpers->form->password(
                    'password',
                    [
                        'id' => 'password',
                        'class' => 'form-control',
                        'required' => true,
                        'autocomplete' => 'current-password',
                    ]
                ) ?>

                <?= $helpers->errorRenderer->field(
                    $helpers->errors,
                    'password'
                ) ?>
            </div>

            <div class="form-group form-group--checkbox">
                <?= $helpers->form->checkbox(
                    'remember',
                    '1',
                    $helpers->old->get('remember') === '1',
                    [
                        'id' => 'remember',
                    ]
                ) ?>

                <?= $helpers->form->label(
                    'remember',
                    'Aangemeld blijven'
                ) ?>
            </div>

            <?= $helpers->form->button(
                'Aanmelden',
                'submit',
                [
                    'class' => 'button button--primary button--block',
                ]
            ) ?>

            <?= $helpers->form->close() ?>
            <?php
        }
    ) ?>
</div>

<?php $this->endSection(); ?>