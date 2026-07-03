<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Models\Event;
use AEFS\Repositories\EventRepository;
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

    public function find(int $id): ?Event
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        $this->validate($data);

        return $this->repository->create(
            $this->sanitize($data)
        );
    }

    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Ongeldig evenement.');
        }

        $this->validate($data);

        $this->repository->update(
            $id,
            $this->sanitize($data)
        );
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Ongeldig evenement.');
        }

        $this->repository->delete($id);
    }

    public function activate(int $id): void
    {
        if (method_exists($this->repository, 'activate')) {
            $this->repository->activate($id);
        }
    }

    public function deactivate(int $id): void
    {
        if (method_exists($this->repository, 'deactivate')) {
            $this->repository->deactivate($id);
        }
    }

    public function count(): int
    {
        if (method_exists($this->repository, 'count')) {
            return $this->repository->count();
        }

        return count($this->all());
    }

    private function validate(array $data): void
    {
        if (empty(trim((string)($data['naam'] ?? '')))) {
            throw new InvalidArgumentException('Naam is verplicht.');
        }

        if (empty($data['startdatum'])) {
            throw new InvalidArgumentException('Startdatum is verplicht.');
        }
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}