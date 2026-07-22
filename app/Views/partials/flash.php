<?php


use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */

$messages = $helpers->flash->messages();
?>

<?php foreach ($messages as $message): ?>
    <div
        class="alert alert--<?= $this->escape($message->type) ?>"
        role="alert"
    >
        <?= $this->escape($message->message) ?>
    </div>
<?php endforeach; ?>