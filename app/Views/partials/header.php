<?php

use AEFS\Core\Auth;

/** @var string|null $title */

$user = Auth::user();

$firstName = is_array($user)
    ? (string) ($user['voornaam'] ?? '')
    : '';

$lastName = is_array($user)
    ? (string) ($user['achternaam'] ?? '')
    : '';

$role = is_array($user)
    ? (string) ($user['rol'] ?? '')
    : '';

$initial = $firstName !== ''
    ? mb_strtoupper(mb_substr($firstName, 0, 1))
    : '?';
?>

<header class="app-header">
    <div class="app-header__content">
        <h1 class="app-header__title">
            <?= $this->escape(
                $title ?? 'AEFS Eventbeheer'
            ) ?>
        </h1>

        <div class="app-header__user">
            <div class="app-header__avatar">
                <?= $this->escape($initial) ?>
            </div>

            <div class="app-header__identity">
                <strong class="app-header__name">
                    <?= $this->escape(
                        trim($firstName . ' ' . $lastName)
                    ) ?>
                </strong>

                <?php if ($role !== ''): ?>
                    <span class="app-header__role">
                        <?= $this->escape(
                            ucfirst($role)
                        ) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>