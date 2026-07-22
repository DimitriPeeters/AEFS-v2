<?php

declare(strict_types=1);

namespace App\ViewComposers;

use AEFS\Core\View\Composer\AbstractViewComposer;

final class NavigationViewComposer extends AbstractViewComposer
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function data(
        string $view,
        array $data
    ): array {
        return [
            'navigationItems' => [
                [
                    'label' => 'Dashboard',
                    'path' => '/dashboard',
                ],
                [
                    'label' => 'Leden',
                    'path' => '/leden',
                ],
                [
                    'label' => 'Gebruikers',
                    'path' => '/gebruikers',
                ],
                [
                    'label' => 'Evenementen',
                    'path' => '/evenementen',
                ],
                [
                    'label' => 'Shiften',
                    'path' => '/shiften',
                ],
                [
                    'label' => 'Inschrijvingen',
                    'path' => '/inschrijvingen',
                ],
                [
                    'label' => 'Mailings',
                    'path' => '/mailings',
                ],
                [
                    'label' => 'Rapporten',
                    'path' => '/rapporten',
                ],
                [
                    'label' => 'Instellingen',
                    'path' => '/instellingen',
                ],
            ],
        ];
    }
}