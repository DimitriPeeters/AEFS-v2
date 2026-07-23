<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\MemberRepository;
use InvalidArgumentException;

final class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private MemberRepository $memberRepository,
        private AuditLogService $auditLog
    ) {
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        return $this->userRepository->all();
    }

    /**
     * @return User[]
     */
    public function search(string $zoek): array
    {
        $zoek = trim($zoek);

        if ($zoek === '') {

            return $this->all();

        }

        return $this->userRepository->search($zoek);
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function create(array $data): int
    {
        $this->validate($data);

        if ($this->userRepository->findByEmail($data['email']) !== null) {

            throw new InvalidArgumentException(
                'Dit e-mailadres is reeds in gebruik.'
            );

        }

        if ($this->memberRepository->find(
            (int)$data['lid_id']
        ) === null) {

            throw new InvalidArgumentException(
                'Ongeldig lid.'
            );

        }

        $data = $this->sanitize($data);

        $id = $this->userRepository->create($data);

        $this->auditLog->created(

            entity: 'user',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            values: [

                'lid_id' => $data['lid_id'],

                'email' => $data['email'],

                'rol' => $data['rol'],

                'actief' => $data['actief']

            ]

        );

        return $id;
    }

    public function update(
        int $id,
        array $data
    ): void {

        $user = $this->find($id);

        if ($user === null) {

            throw new InvalidArgumentException(
                'Gebruiker niet gevonden.'
            );

        }

        $this->validate($data, false);

        $data = $this->sanitize($data);

        $this->userRepository->update(
            $id,
            $data
        );

        $this->auditLog->updated(

            entity: 'user',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            oldValues: get_object_vars($user),

            newValues: [

                'lid_id' => $data['lid_id'],

                'email' => $data['email'],

                'rol' => $data['rol'],

                'actief' => $data['actief']

            ]

        );
    }

    public function delete(int $id): void
    {
        $user = $this->find($id);

        if ($user === null) {

            return;

        }

        $this->userRepository->delete($id);

        $this->auditLog->deleted(

            entity: 'user',

            id: $id,

            userId: $_SESSION['user_id'] ?? null,

            oldValues: get_object_vars($user)

        );
    }

    public function updateLogin(int $id): void
    {
        $this->userRepository->updateLogin($id);
    }

    private function validate(
        array $data,
        bool $creating = true
    ): void {

        if (empty($data['lid_id'])) {

            throw new InvalidArgumentException(
                'Selecteer een lid.'
            );

        }

        if (empty($data['email'])) {

            throw new InvalidArgumentException(
                'E-mailadres is verplicht.'
            );

        }

        if (!filter_var(
            $data['email'],
            FILTER_VALIDATE_EMAIL
        )) {

            throw new InvalidArgumentException(
                'Ongeldig e-mailadres.'
            );

        }

        if ($creating && empty($data['password'])) {

            throw new InvalidArgumentException(
                'Wachtwoord is verplicht.'
            );

        }

        if (
            !empty($data['password']) &&
            strlen($data['password']) < 8
        ) {

            throw new InvalidArgumentException(
                'Het wachtwoord moet minstens 8 tekens bevatten.'
            );

        }

        $rollen = [

            User::ROLE_ADMIN,

            User::ROLE_EVENTMANAGER,

            User::ROLE_COORDINATOR,

            User::ROLE_MEMBER

        ];

        if (!in_array(
            $data['rol'],
            $rollen,
            true
        )) {

            throw new InvalidArgumentException(
                'Ongeldige rol.'
            );

        }
    }

    private function sanitize(array $data): array
    {
        $data['email'] = strtolower(
            trim($data['email'])
        );

        $data['actief'] = !empty(
            $data['actief']
        );

        return $data;
    }
}