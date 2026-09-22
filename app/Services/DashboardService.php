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
        $memberId = Auth::memberId() ?? 0;

        $data = [
            'isAdmin' => $isAdmin,
            'statistics' => [
                'events' => $isAdmin
                    ? $this->dashboardRepository->countUpcomingEvents()
                    : $this->dashboardRepository
                        ->countUpcomingEventsForMember($memberId),
                'shifts' => $isAdmin
                    ? $this->dashboardRepository->countOpenShifts()
                    : $this->dashboardRepository
                        ->countOpenShiftsForMember($memberId),
            ],
            'upcomingEvents' => $isAdmin
                ? $this->dashboardRepository->upcomingEvents()
                : $this->dashboardRepository
                    ->upcomingEventsForMember($memberId),
            'openShifts' => $isAdmin
                ? $this->dashboardRepository->openShifts()
                : [],
            'latestMembers' => [],
            'pendingRegistrations' => [],
            'pendingEventCancellations' => [],
            'pendingEventRegistrations' => [],
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

        $data['statistics']['eventCancellations'] = $this
            ->dashboardRepository
            ->countPendingEventCancellations();

        $data['statistics']['eventRegistrations'] = $this
            ->dashboardRepository
            ->countPendingEventRegistrations();

        $data['latestMembers'] = $this->dashboardRepository
            ->latestApprovedMembers();

        $data['pendingRegistrations'] = $this->dashboardRepository
            ->pendingRegistrations();

        $data['pendingEventCancellations'] = $this->dashboardRepository
            ->pendingEventCancellations();

        $data['pendingEventRegistrations'] = $this->dashboardRepository
            ->pendingEventRegistrations();

        return $data;
    }
}
