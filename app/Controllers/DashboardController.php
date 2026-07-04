<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DashboardService;

final class DashboardController extends BaseController
{
    public function __construct(
        private DashboardService $dashboardService
    ) {
    }

    public function index(): void
    {
        $dashboard = $this->dashboardService->getDashboardData();

        $this->view(
            'dashboard.index',
            [
                'title'           => 'Dashboard',
                'statistics'      => $dashboard['statistics'],
                'latestMembers'   => $dashboard['latestMembers'],
                'upcomingEvents'  => $dashboard['upcomingEvents'],
                'openShifts'      => $dashboard['openShifts'],
            ]
        );
    }
}