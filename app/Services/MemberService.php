<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Member;
use App\Repositories\MemberRepository;
use App\Validators\MemberValidator;
use InvalidArgumentException;

final class MemberService
{
    public function __construct(
        private MemberRepository $repository,
        private MemberValidator $validator,
        private AuditLogService $auditLog
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
        $this->validator->validate($data);

        $data = $this->sanitize($data);

        $id = $this->repository->create($data);

        $this->auditLog->created(

            entity: 'member',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            values: $data

        );

        return $id;
    }

    public function update(
        int $id,
        array $data
    ): void {

        if ($id <= 0) {

            throw new InvalidArgumentException(
                'Ongeldig lid.'
            );

        }

        $this->validator->validate($data);

        $oud = $this->repository->find($id);

        if ($oud === null) {

            throw new InvalidArgumentException(
                'Lid niet gevonden.'
            );

        }

        $data = $this->sanitize($data);

        $this->repository->update(
            $id,
            $data
        );

        $this->auditLog->updated(

            entity: 'member',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            oldValues: get_object_vars($oud),

            newValues: $data

        );
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {

            throw new InvalidArgumentException(
                'Ongeldig lid.'
            );

        }

        $lid = $this->repository->find($id);

        if ($lid === null) {

            return;

        }

        $this->repository->delete($id);

        $this->auditLog->deleted(

            entity: 'member',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            oldValues: get_object_vars($lid)

        );
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {

            if (is_string($value)) {

                $data[$key] = trim($value);

            }

        }

$data['actief'] = filter_var(
    $data['actief'] ?? false,
    FILTER_VALIDATE_BOOL
);

$data['gdpr_consent'] = filter_var(
    $data['gdpr_consent'] ?? false,
    FILTER_VALIDATE_BOOL
);
        if (empty($data['land'])) {

            $data['land'] = 'België';

        }

        return $data;
    }
}