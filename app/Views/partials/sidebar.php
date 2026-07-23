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
?>

<aside class="sidebar">
    <div class="sidebar__brand">
        <a
            class="sidebar__brand-link"
            href="<?= $this->escape(
                $helpers->url->to('/dashboard')
            ) ?>"
        >
            AEFS
        </a>
    </div>

    <nav
        class="sidebar__navigation"
        aria-label="Hoofdnavigatie"
    >
        <ul class="sidebar__menu">
            <?php foreach ($items as $item): ?>
                <li class="sidebar__item">
                    <a
                        class="sidebar__link"
                        href="<?= $this->escape(
                            $helpers->url->to($item['path'])
                        ) ?>"
                    >
                        <?= $this->escape($item['label']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>