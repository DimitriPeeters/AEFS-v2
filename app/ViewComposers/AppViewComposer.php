<?php

declare(strict_types=1);

namespace App\ViewComposers;

use AEFS\Core\View\Composer\AbstractViewComposer;

final class AppViewComposer extends AbstractViewComposer
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
            'applicationName' => 'AEFS Eventbeheer',
            'currentYear' => (int) date('Y'),
        ];
    }
}