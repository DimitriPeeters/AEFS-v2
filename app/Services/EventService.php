<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use InvalidArgumentException;

final class EventService
{
    public function __construct(
        private EventRepository $repository
    ) {
    }

    /**
     * @return Event[]
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * @return Event[]
     */
    public function search(string $zoekterm): array
    {
        $zoekterm = trim($zoekterm);

        if ($zoekterm === '') {
            return $this->all();
        }

        return $this->repository->search($zoekterm);
    }

    /**
     * @return Event[]
     */
    public function paginate(
        int $page = 1,
        int $perPage = 25
    ): array {

        return $this->repository->paginate(
            $page,
            $perPage
        );
    }

    public function count(): int
    {
        return $this->repository->count();
    }

    public function find(int $id): ?Event
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        $data = $this->sanitize($data);

        $this->validate($data);

        return $this->repository->create($data);
    }

    public function update(
        int $id,
        array $data
    ): void {

        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldig evenement.'
            );
        }

        $data = $this->sanitize($data);

        $this->validate($data);

        $this->repository->update(
            $id,
            $data
        );
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldig evenement.'
            );
        }

        $this->repository->delete($id);
    }

    private function validate(array $data): void
    {
        if (empty(trim((string) ($data['titel'] ?? '')))) {
            throw new InvalidArgumentException(
                'Titel is verplicht.'
            );
        }

        if (empty($data['start_datum'])) {
            throw new InvalidArgumentException(
                'Startdatum is verplicht.'
            );
        }

        if (
            !empty($data['eind_datum']) &&
            $data['eind_datum'] < $data['start_datum']
        ) {
            throw new InvalidArgumentException(
                'De einddatum mag niet vóór de startdatum liggen.'
            );
        }
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {

            if (is_string($value)) {

                $data[$key] = trim($value);

            }

        }

        $data['titel'] = $data['titel'] ?? '';

        $data['omschrijving'] = $data['omschrijving'] ?? null;

        $data['locatie'] = $data['locatie'] ?? null;

        $data['eind_datum'] = $data['eind_datum'] ?: null;

        $data['actief'] = !empty($data['actief']) ? 1 : 0;

        return $data;
    }
}