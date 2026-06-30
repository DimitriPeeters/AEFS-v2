<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\Database;
use AEFS\Models\User;
use PDO;

final class UserRepository
{
    public function __construct(
        private Database $database
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->database->pdo()->prepare(
            "SELECT id,
                    naam,
                    email,
                    wachtwoord,
                    rol
             FROM gebruikers
             WHERE email = :email
             LIMIT 1"
        );

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        return new User(
            (int) $user['id'],
            $user['naam'],
            $user['email'],
            $user['rol'],
            $user['wachtwoord']
        );
    }
}