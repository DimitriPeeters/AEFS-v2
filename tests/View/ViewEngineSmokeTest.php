<?php

declare(strict_types=1);

namespace Tests\View;

use AEFS\Core\View\ViewEngineInterface;
use RuntimeException;

final readonly class ViewEngineSmokeTest
{
    public function __construct(
        private ViewEngineInterface $view
    ) {
    }

    public function run(): void
    {
        $this->assertViewExists('layouts.base');
        $this->assertViewExists('layouts.app');
        $this->assertViewExists('layouts.guest');

        $this->assertViewExists('partials.header');
        $this->assertViewExists('partials.sidebar');
        $this->assertViewExists('partials.flash');
        $this->assertViewExists('partials.errors');

        $this->assertViewExists('components.card');
        $this->assertViewExists('components.button');
        $this->assertViewExists('components.link-button');
        $this->assertViewExists('components.alert');

        $this->assertViewExists('auth.login');
        $this->assertViewExists('dashboard.index');

        $html = $this->view->render(
            'dashboard.index',
            [
                'statistics' => [
                    'members' => 12,
                    'users' => 9,
                    'events' => 4,
                    'shifts' => 18,
                ],
                'latestMembers' => [
                    [
                        'voornaam' => 'Test',
                        'achternaam' => 'Gebruiker',
                        'gemeente' => 'Mechelen',
                    ],
                ],
                'upcomingEvents' => [
                    [
                        'titel' => 'Testevenement',
                        'startdatum' => '2026-08-01',
                    ],
                ],
                'openShifts' => [
                    [
                        'naam' => 'Steward',
                        'shift_datum' => '2026-08-01',
                    ],
                ],
            ]
        );

        $this->assertContains(
            '<!DOCTYPE html>',
            $html
        );

        $this->assertContains(
            'Dashboard',
            $html
        );

        $this->assertContains(
            '12',
            $html
        );

        $this->assertContains(
            '9',
            $html
        );

        $this->assertContains(
            '4',
            $html
        );

        $this->assertContains(
            '18',
            $html
        );

        $this->assertContains(
            'Test Gebruiker',
            $html
        );

        $this->assertContains(
            'Testevenement',
            $html
        );

        $this->assertContains(
            'Steward',
            $html
        );

        $this->assertContains(
            'sidebar',
            $html
        );

        $this->assertContains(
            'app__content',
            $html
        );
    }

    private function assertViewExists(string $view): void
    {
        if ($this->view->exists($view)) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Verplichte view [%s] bestaat niet.',
                $view
            )
        );
    }

    private function assertContains(
        string $expected,
        string $actual
    ): void {
        if (str_contains($actual, $expected)) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'Verwachte inhoud [%s] werd niet gerenderd.',
                $expected
            )
        );
    }
}