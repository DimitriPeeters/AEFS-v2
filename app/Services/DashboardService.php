<?php

declare(strict_types=1);

namespace App\Services;

final class DashboardService
{
    public function getDashboardData(): array
    {
        return [
            'statistics' => [
                'members' => 0,
                'users' => 0,
                'events' => 0,
                'shifts' => 0,
            ],
            'latestMembers' => [],
            'upcomingEvents' => [],
            'openShifts' => [],
        ];
    }
}