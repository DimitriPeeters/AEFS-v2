<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Repositories\DashboardRepository;

final class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {
    }

    public function getDashboardData(): array
    {
        return [

            'statistics' => $this->repository->statistics(),

            'latestMembers' => $this->repository->latestMembers(5),

            'upcomingEvents' => $this->repository->upcomingEvents(5),

            'openShifts' => $this->repository->openShifts(5),

        ];
    }

    public function statistics(): array
    {
        return $this->repository->statistics();
    }

    public function latestMembers(
        int $limit = 5
    ): array {
        return $this->repository->latestMembers($limit);
    }

    public function upcomingEvents(
        int $limit = 5
    ): array {
        return $this->repository->upcomingEvents($limit);
    }

    public function openShifts(
        int $limit = 5
    ): array {
        return $this->repository->openShifts($limit);
    }
}