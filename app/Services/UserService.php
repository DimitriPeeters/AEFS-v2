<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Repositories\UserRepository;
use AEFS\Models\User;

final class UserService
{
    public function __construct(
        private UserRepository $repository
    ){
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function search(string $zoekterm): array
    {
        return $zoekterm === ''
            ? $this->all()
            : $this->repository->search($zoekterm);
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        return $this->repository->create($data);
    }

    public function update(int $id,array $data): void
    {
        $this->repository->update($id,$data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }
}