<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Database\DB;

final class DashboardRepository
{
    public function countMembers(): int
    {
        return (int) DB::table('leden')->count();
    }

    public function countUsers(): int
    {
        return (int) DB::table('gebruikers')->count();
    }

    public function countEvents(): int
    {
        return (int) DB::table('evenementen')->count();
    }

    public function countOpenShifts(): int
    {
        return (int) DB::table('event_shifts')->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latestMembers(int $limit = 5): array
    {
        return DB::table('leden')
            ->orderBy('lid_id', 'DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function upcomingEvents(int $limit = 5): array
    {
        return DB::table('evenementen')
            ->orderBy('startdatum', 'ASC')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function openShifts(int $limit = 5): array
    {
        return DB::table('event_shifts')
            ->orderBy('shift_id', 'DESC')
            ->limit($limit)
            ->get();
    }
}