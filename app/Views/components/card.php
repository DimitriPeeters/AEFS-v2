<?php


use AEFS\Core\View\Component\Slot;
use AEFS\Core\View\Component\SlotBag;

/** @var Slot $slot */
/** @var SlotBag $slots */
/** @var string|null $class */

$classes = trim('card ' . ($class ?? ''));
?>
<section class="<?= $this->escape($classes) ?>">
    <?php if ($slots->has('header')): ?>
        <header class="card__header">
            <?= $slots->get('header') ?>
        </header>
    <?php endif; ?>

    <div class="card__body">
        <?= $slot ?>
    </div>

    <?php if ($slots->has('footer')): ?>
        <footer class="card__footer">
            <?= $slots->get('footer') ?>
        </footer>
    <?php endif; ?>
</section>