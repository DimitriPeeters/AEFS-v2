<?php


use AEFS\Core\View\Component\Slot;

/** @var Slot $slot */
/** @var string|null $type */
/** @var string|null $variant */
/** @var string|null $class */
/** @var bool|null $disabled */

$buttonType = $type ?? 'button';
$buttonVariant = $variant ?? 'primary';
$buttonClass = trim(
    sprintf(
        'button button--%s %s',
        $buttonVariant,
        $class ?? ''
    )
);
?>
<button
    type="<?= $this->escape($buttonType) ?>"
    class="<?= $this->escape($buttonClass) ?>"
    <?= ($disabled ?? false) ? 'disabled' : '' ?>
>
    <?= $slot ?>
</button>