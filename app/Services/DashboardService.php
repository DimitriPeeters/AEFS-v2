<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;

final class DashboardService
{
    public function __construct(
        private DashboardRepository $dashboardRepository
    ) {
    }

    public function getDashboardData(): array
    {
        return [
            'statistics' => [
                'members' => $this->dashboardRepository->countMembers(),
                'users' => $this->dashboardRepository->countUsers(),
                'events' => $this->dashboardRepository->countEvents(),
                'shifts' => $this->dashboardRepository->countOpenShifts(),
            ],
            'latestMembers' => $this->dashboardRepository->latestMembers(),
            'upcomingEvents' => $this->dashboardRepository->upcomingEvents(),
            'openShifts' => $this->dashboardRepository->openShifts(),
        ];
    }
}