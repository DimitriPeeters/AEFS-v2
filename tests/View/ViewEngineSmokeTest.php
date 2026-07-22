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
                'memberCount' => 12,
                'eventCount' => 4,
                'shiftCount' => 18,
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
            '4',
            $html
        );

        $this->assertContains(
            '18',
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