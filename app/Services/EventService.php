<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use App\Models\Event;
use App\Repositories\EventRepository;
use App\Validators\EvenementenValidator;
use DomainException;
use InvalidArgumentException;

final class EventService
{
    public function __construct(
        private readonly EventRepository $repository,
        private readonly EvenementenValidator $validator,
        private readonly AuditLogService $auditLog
    ) {
    }

    /**
     * @return Event[]
     */
    public function allForAdministration(): array
    {
        return $this->repository->allForAdministration();
    }

    /**
     * @return Event[]
     */
    public function visibleToMembers(): array
    {
        return $this->repository->visibleToMembers();
    }

    /**
     * @return Event[]
     */
    public function searchForAdministration(string $zoekterm): array
    {
        $zoekterm = trim($zoekterm);

        return $zoekterm === ''
            ? $this->allForAdministration()
            : $this->repository->searchForAdministration($zoekterm);
    }

    /**
     * @return Event[]
     */
    public function searchVisibleToMembers(string $zoekterm): array
    {
        $zoekterm = trim($zoekterm);

        return $zoekterm === ''
            ? $this->visibleToMembers()
            : $this->repository->searchVisibleToMembers($zoekterm);
    }

    public function find(int $id): ?Event
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function findVisibleToMembers(int $id): ?Event
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findVisibleToMembers($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $this->validator->validate($data);

        $id = $this->repository->create($data);

        $this->auditLog->created(
            entity: 'event',
            id: $id,
            userId: Auth::id(),
            values: $data
        );

        return $id;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(
        int $id,
        array $data
    ): void {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldig evenement.'
            );
        }

        $this->validator->validate($data);

        $event = $this->repository->find($id);

        if ($event === null) {
            throw new InvalidArgumentException(
                'Evenement niet gevonden.'
            );
        }

        $this->repository->update(
            $id,
            $data
        );

        $this->auditLog->updated(
            entity: 'event',
            id: $id,
            userId: Auth::id(),
            oldValues: $event->toAuditArray(),
            newValues: $data
        );
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldig evenement.'
            );
        }

        $event = $this->repository->find($id);

        if ($event === null) {
            throw new InvalidArgumentException(
                'Evenement niet gevonden.'
            );
        }

        $related = $this->repository->relatedDataCounts($id);

        if (
            $related['inschrijvingen'] > 0
            || $related['shifts'] > 0
        ) {
            throw new DomainException(
                'Dit evenement heeft inschrijvingen of shifts en kan daarom niet worden verwijderd. Zet de status op geannuleerd.'
            );
        }

        $this->repository->delete($id);

        $this->auditLog->deleted(
            entity: 'event',
            id: $id,
            userId: Auth::id(),
            oldValues: $event->toAuditArray()
        );
    }
}
