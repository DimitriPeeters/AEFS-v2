<?php


use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */
/** @var int|null $memberCount */
/** @var int|null $eventCount */
/** @var int|null $shiftCount */

$this->extend('layouts.app', [
    'title' => 'Dashboard',
]);
?>

<?php $this->startSection('content'); ?>

<div class="dashboard">
    <div class="dashboard__header">
        <div>
            <h2 class="dashboard__title">Dashboard</h2>

            <p class="dashboard__description">
                Overzicht van AEFS Eventbeheer.
            </p>
        </div>

        <?= $this->component(
            'link-button',
            [
                'href' => $helpers->url->to('/evenementen/nieuw'),
                'variant' => 'primary',
            ],
            static function (): void {
                echo 'Nieuw evenement';
            }
        ) ?>
    </div>

    <div class="dashboard__grid">
        <?= $this->component(
            'card',
            ['class' => 'dashboard-card'],
            function () use ($memberCount): void {
                $this->startSlot('header');
                ?>
                <h3 class="dashboard-card__title">Leden</h3>
                <?php
                $this->endSlot();

                ?>
                <div class="dashboard-card__value">
                    <?= $this->escape($memberCount ?? 0) ?>
                </div>
                <?php

                $this->startSlot('footer');
                ?>
                <a href="/leden">Bekijk alle leden</a>
                <?php
                $this->endSlot();
            }
        ) ?>

        <?= $this->component(
            'card',
            ['class' => 'dashboard-card'],
            function () use ($eventCount): void {
                $this->startSlot('header');
                ?>
                <h3 class="dashboard-card__title">Evenementen</h3>
                <?php
                $this->endSlot();

                ?>
                <div class="dashboard-card__value">
                    <?= $this->escape($eventCount ?? 0) ?>
                </div>
                <?php

                $this->startSlot('footer');
                ?>
                <a href="/evenementen">Bekijk alle evenementen</a>
                <?php
                $this->endSlot();
            }
        ) ?>

        <?= $this->component(
            'card',
            ['class' => 'dashboard-card'],
            function () use ($shiftCount): void {
                $this->startSlot('header');
                ?>
                <h3 class="dashboard-card__title">Shiften</h3>
                <?php
                $this->endSlot();

                ?>
                <div class="dashboard-card__value">
                    <?= $this->escape($shiftCount ?? 0) ?>
                </div>
                <?php

                $this->startSlot('footer');
                ?>
                <a href="/shiften">Bekijk alle shiften</a>
                <?php
                $this->endSlot();
            }
        ) ?>
    </div>
</div>

<?php $this->endSection(); ?>

<?php $this->startSection('scripts'); ?>

<script>
    document.documentElement.classList.add('view-engine-active');
</script>

<?php $this->endSection(); ?>