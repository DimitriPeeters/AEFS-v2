<?php

use AEFS\Core\View\Helper\ViewHelpers;

/** @var ViewHelpers $helpers */
/** @var list<array{label: string, path: string}>|null $navigationItems */

$items = $navigationItems ?? [
    [
        'label' => 'Dashboard',
        'path' => '/dashboard',
    ],
    [
        'label' => 'Leden',
        'path' => '/members',
    ],
    [
        'label' => 'Gebruikers',
        'path' => '/users',
    ],
    [
        'label' => 'Evenementen',
        'path' => '/events',
    ],
    [
        'label' => 'Shiften',
        'path' => '/shifts',
    ],
    [
        'label' => 'Inschrijvingen',
        'path' => '/registrations',
    ],
    [
        'label' => 'Mailings',
        'path' => '/mailings',
    ],
    [
        'label' => 'Rapporten',
        'path' => '/reports',
    ],
    [
        'label' => 'Instellingen',
        'path' => '/settings',
    ],
];

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '',
    PHP_URL_PATH
);

$currentPath = is_string($currentPath)
    ? $currentPath
    : '';
?>

<aside class="sidebar">
    <div class="sidebar__brand">
        <a
            class="sidebar__brand-link"
            href="<?= $this->escape(
                $helpers->url->to('/dashboard')
            ) ?>"
        >
            <img
                class="sidebar__logo"
                src="<?= $this->escape(
                    $helpers->asset->url(
                        'images/aefs-logo-white.png'
                    )
                ) ?>"
                alt="AEFS"
            >

            <span class="sidebar__brand-text">
                Eventbeheer
            </span>
        </a>
    </div>

    <nav
        class="sidebar__navigation"
        aria-label="Hoofdnavigatie"
    >
        <ul class="sidebar__menu">
            <?php foreach ($items as $item): ?>
                <?php
                $itemUrl = $helpers->url->to($item['path']);

                $active = $currentPath === $itemUrl
                    || (
                        $item['path'] !== '/dashboard'
                        && str_starts_with(
                            $currentPath,
                            rtrim($itemUrl, '/') . '/'
                        )
                    );
                ?>

                <li class="sidebar__item">
                    <a
                        class="sidebar__link<?= $active ? ' sidebar__link--active' : '' ?>"
                        href="<?= $this->escape($itemUrl) ?>"
                    >
                        <span class="sidebar__link-label">
                            <?= $this->escape($item['label']) ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar__footer">
        <span>AEFS v2</span>
    </div>
</aside>