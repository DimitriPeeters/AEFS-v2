<?php


use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */
?>

<?php if ($helpers->errors->any()): ?>
    <div
        class="alert alert--error"
        role="alert"
    >
        <strong>Controleer de ingevoerde gegevens.</strong>

        <?= $helpers->errorRenderer->list(
            $helpers->errors,
            'alert__errors'
        ) ?>
    </div>
<?php endif; ?>