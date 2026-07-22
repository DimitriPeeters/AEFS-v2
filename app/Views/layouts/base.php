
<?php


use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */
/** @var string|null $title */
/** @var string|null $applicationName */

$pageTitle = trim((string) ($title ?? ''));

if ($pageTitle === '') {
    $pageTitle = $applicationName ?? 'AEFS Eventbeheer';
} else {
    $pageTitle .= ' | ' . ($applicationName ?? 'AEFS Eventbeheer');
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $this->escape($pageTitle) ?></title>

    <?= $helpers->asset->css('css/app.css') ?>

    <?= $this->section('styles') ?>
</head>
<body class="<?= $this->escape($bodyClass ?? '') ?>">
    <?= $this->section('body') ?>

    <?= $helpers->asset->js('js/app.js') ?>

    <?= $this->section('scripts') ?>
</body>
</html>