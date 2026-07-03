<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Models\Member;
use AEFS\Repositories\MemberRepository;
use InvalidArgumentException;

final class MemberService
{
    public function __construct(
        private MemberRepository $repository
    ) {
    }

    /**
     * @return Member[]
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * @return Member[]
     */
    public function search(string $zoekterm): array
    {
        $zoekterm = trim($zoekterm);

        if ($zoekterm === '') {
            return $this->all();
        }

        return $this->repository->search($zoekterm);
    }

    public function find(int $id): ?Member
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        $this->validate($data);

        return $this->repository->create($this->sanitize($data));
    }

    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Ongeldig lid.');
        }

        $this->validate($data);

        $this->repository->update($id, $this->sanitize($data));
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Ongeldig lid.');
        }

        $this->repository->delete($id);
    }

    private function validate(array $data): void
    {
        if (empty(trim((string)($data['voornaam'] ?? '')))) {
            throw new InvalidArgumentException('Voornaam is verplicht.');
        }

        if (empty(trim((string)($data['achternaam'] ?? '')))) {
            throw new InvalidArgumentException('Achternaam is verplicht.');
        }

        $email = trim((string)($data['email'] ?? ''));

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ongeldig e-mailadres.');
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