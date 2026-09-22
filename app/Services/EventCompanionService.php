<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use AEFS\Core\Database;
use App\Repositories\EventCompanionRepository;
use DomainException;
use InvalidArgumentException;

final class EventCompanionService
{
    public function __construct(
        private readonly Database $database,
        private readonly EventCompanionRepository $repository,
        private readonly AuditLogService $auditLog
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function choicesForMember(int $memberId): array
    {
        $events = $this->repository->invitationsForMember($memberId);
        foreach ($events as &$event) {
            $eventId = (int) $event['event_id'];
            $event['open'] = (int) $event['open'] === 1;
            $event['selected'] = $this->repository->selectedIds($eventId, $memberId);
            $event['members'] = array_values(array_filter(
                $this->repository->confirmedMembers($eventId),
                static fn(array $member): bool => $member['lid_id'] !== $memberId
            ));
        }
        unset($event);

        return $events;
    }

    /** @param int[] $selectedIds */
    public function saveChoices(int $eventId, int $memberId, array $selectedIds): void
    {
        $this->database->transaction(function () use ($eventId, $memberId, $selectedIds): void {
            $invitation = $this->repository->deliveredInvitation($eventId, $memberId);
            if ($invitation === null || (int) $invitation['open'] !== 1) {
                throw new DomainException(
                    'De termijn voor deze shiftvoorkeuren is verlopen of de bevestigingsmail werd nog niet afgeleverd.'
                );
            }

            $selectedIds = array_values(array_unique(array_map('intval', $selectedIds)));
            $allowed = array_column(
                $this->repository->confirmedMembers($eventId),
                'lid_id'
            );
            foreach ($selectedIds as $selectedId) {
                if ($selectedId === $memberId || !in_array($selectedId, $allowed, true)) {
                    throw new InvalidArgumentException(
                        'Kies uitsluitend andere bevestigde deelnemers van dit evenement.'
                    );
                }
            }

            $before = $this->repository->selectedIds($eventId, $memberId);
            sort($before);
            sort($selectedIds);
            if ($before === $selectedIds) {
                return;
            }

            $this->repository->replaceSelections(
                $eventId,
                $memberId,
                (int) $invitation['voorkeur_mailing_id'],
                $selectedIds
            );
            $this->auditLog->updated(
                entity: 'event_shift_preferences',
                id: (int) $invitation['inschrijving_id'],
                userId: Auth::id(),
                oldValues: ['event_id' => $eventId, 'selected_member_ids' => $before],
                newValues: ['event_id' => $eventId, 'selected_member_ids' => $selectedIds]
            );
        });
    }

    /** @return int[] */
    public function selectedIds(int $eventId, int $memberId): array
    {
        return $this->repository->selectedIds($eventId, $memberId);
    }

    /** @return array<int, array{lid_id: int, voornaam: string, achternaam: string}> */
    public function confirmedMembers(int $eventId, ?string $date = null): array
    {
        return $this->repository->confirmedMembers($eventId, $date);
    }

    /** @return array<int, array{lid_id: int, gewenst_lid_id: int}> */
    public function edges(int $eventId): array
    {
        return $this->repository->edges($eventId);
    }
}
