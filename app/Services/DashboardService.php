<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use App\Repositories\DashboardRepository;

final class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $isAdmin = Auth::isAdmin();

        $data = [
            'isAdmin' => $isAdmin,
            'statistics' => [
                'events' => $this->dashboardRepository
                    ->countUpcomingEvents(),
                'shifts' => $this->dashboardRepository
                    ->countOpenShifts(),
            ],
            'upcomingEvents' => $this->dashboardRepository
                ->upcomingEvents(),
            'openShifts' => $this->dashboardRepository
                ->openShifts(),
            'latestMembers' => [],
            'pendingRegistrations' => [],
        ];

        if (!$isAdmin) {
            return $data;
        }

        $data['statistics']['members'] = $this->dashboardRepository
            ->countActiveMembers();

        $data['statistics']['pending'] = $this->dashboardRepository
            ->countPendingRegistrations();

        $data['statistics']['users'] = $this->dashboardRepository
            ->countActiveUsers();

        $data['latestMembers'] = $this->dashboardRepository
            ->latestApprovedMembers();

        $data['pendingRegistrations'] = $this->dashboardRepository
            ->pendingRegistrations();

        return $data;
    }
}