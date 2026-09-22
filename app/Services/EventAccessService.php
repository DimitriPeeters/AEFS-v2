<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use App\Repositories\EventAccessRepository;
use DomainException;
use InvalidArgumentException;

final class EventAccessService
{
    public function __construct(
        private readonly EventAccessRepository $repository
    ) {
    }

    public function canManage(int $eventId): bool
    {
        if (Auth::isAdmin()) {
            return true;
        }

        $memberId = Auth::memberId();

        return $memberId !== null
            && $this->repository->canManage($memberId, $eventId);
    }

    public function hasManagementAccess(): bool
    {
        if (Auth::isAdmin()) {
            return true;
        }

        $memberId = Auth::memberId();

        return $memberId !== null
            && $this->repository->hasManagedEvents($memberId);
    }

    public function requireManage(int $eventId): void
    {
        if (!$this->canManage($eventId)) {
            throw new DomainException(
                'Je hebt geen beheerrechten voor dit evenement.'
            );
        }
    }

    /** @return int[] */
    public function managedEventIds(): array
    {
        if (Auth::isAdmin()) {
            return [];
        }

        $memberId = Auth::memberId();

        return $memberId !== null
            ? $this->repository->managedEventIds($memberId)
            : [];
    }

    /** @return array<int, array{id: int, label: string, email: string}> */
    public function managerOptions(): array
    {
        return $this->repository->managerOptions();
    }

    /** @return array<int, array{id: int, label: string}> */
    public function groupOptions(): array
    {
        return $this->repository->groupOptions();
    }

    /** @return int[] */
    public function managerIds(int $eventId): array
    {
        return $this->repository->managerIds($eventId);
    }

    /** @return int[] */
    public function groupIds(int $eventId): array
    {
        return $this->repository->groupIds($eventId);
    }

    /** @param int[] $managerIds @param int[] $groupIds */
    public function syncAssignments(
        int $eventId,
        array $managerIds,
        array $groupIds
    ): void {
        if (!Auth::isAdmin()) {
            throw new DomainException(
                'Alleen een administrator kan eventbeheerders en zichtbaarheidsgroepen wijzigen.'
            );
        }

        $managerIds = $this->normalizeIds($managerIds);
        $groupIds = $this->normalizeIds($groupIds);

        if ($this->repository->validManagerIds($managerIds) !== $managerIds) {
            throw new InvalidArgumentException(
                'Een of meer gekozen eventbeheerders zijn niet actief of niet goedgekeurd.'
            );
        }

        if ($this->repository->validGroupIds($groupIds) !== $groupIds) {
            throw new InvalidArgumentException(
                'Een of meer gekozen zichtbaarheidsgroepen bestaan niet meer.'
            );
        }

        $this->repository->syncManagers($eventId, $managerIds, Auth::id());
        $this->repository->syncGroups($eventId, $groupIds);
    }

    /** @param mixed[] $ids @return int[] */
    private function normalizeIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn(int $id): bool => $id > 0
        )));
        sort($ids);

        return $ids;
    }
}
