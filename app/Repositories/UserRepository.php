<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use App\Models\User;
use PDO;

final class UserRepository extends BaseRepository
{
    protected string $table = 'gebruikers';

    protected string $primaryKey = 'gebruiker_id';

    public function __construct(
        Database $database
    ) {
        parent::__construct($database);
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        $stmt = $this->database->prepare("
            SELECT
                g.*,
                l.voornaam,
                l.achternaam
            FROM gebruikers g
            INNER JOIN leden l
                ON l.lid_id = g.lid_id
            ORDER BY
                l.voornaam,
                l.achternaam
        ");

        $stmt->execute();

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return User[]
     */
    public function search(string $zoek): array
    {
        $stmt = $this->database->prepare("
            SELECT
                g.*,
                l.voornaam,
                l.achternaam
            FROM gebruikers g
            INNER JOIN leden l
                ON l.lid_id = g.lid_id
            WHERE

                l.voornaam LIKE :zoek

                OR l.achternaam LIKE :zoek

                OR g.email LIKE :zoek

            ORDER BY

                l.voornaam,
                l.achternaam
        ");

        $stmt->execute([

            'zoek' => '%' . $zoek . '%'

        ]);

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?User
    {
        $stmt = $this->database->prepare("
            SELECT
                g.*,
                l.voornaam,
                l.achternaam
            FROM gebruikers g
            INNER JOIN leden l
                ON l.lid_id = g.lid_id
            WHERE g.gebruiker_id = ?
        ");

        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? $this->map($row)
            : null;
    }

    public function findByEmail(
        string $email
    ): ?User {

        $stmt = $this->database->prepare("
            SELECT
                g.*,
                l.voornaam,
                l.achternaam
            FROM gebruikers g
            INNER JOIN leden l
                ON l.lid_id = g.lid_id
            WHERE g.email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? $this->map($row)
            : null;
    }

    public function create(
        array $data
    ): int {

        $stmt = $this->database->prepare("
            INSERT INTO gebruikers
            (
                lid_id,
                email,
                wachtwoord_hash,
                rol,
                actief,
                laatste_login,
                laatste_ip
            )
            VALUES
            (
                :lid_id,
                :email,
                :wachtwoord_hash,
                :rol,
                :actief,
                NULL,
                NULL
            )
        ");

        $stmt->execute([

            'lid_id' => $data['lid_id'],

            'email' => $data['email'],

            'wachtwoord_hash' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            ),

            'rol' => $data['rol'],

            'actief' => !empty($data['actief'])

        ]);

        return (int)$this->database->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): void {

        $sql = "

            UPDATE gebruikers

            SET

                lid_id = :lid_id,

                email = :email,

                rol = :rol,

                actief = :actief

        ";

        if (!empty($data['password'])) {

            $sql .= ",

                wachtwoord_hash = :password

            ";

        }

        $sql .= "

            WHERE gebruiker_id = :id

        ";

        $stmt = $this->database->prepare($sql);

        $params = [

            'id' => $id,

            'lid_id' => $data['lid_id'],

            'email' => $data['email'],

            'rol' => $data['rol'],

            'actief' => !empty($data['actief'])

        ];

        if (!empty($data['password'])) {

            $params['password'] = password_hash(

                $data['password'],

                PASSWORD_DEFAULT

            );

        }

        $stmt->execute($params);
    }

    public function delete(
        int $id
    ): void {

        $stmt = $this->database->prepare("
            DELETE
            FROM gebruikers
            WHERE gebruiker_id = ?
        ");

        $stmt->execute([$id]);
    }

    public function updateLogin(
        int $id
    ): void {

        $stmt = $this->database->prepare("
            UPDATE gebruikers
            SET

                laatste_login = NOW(),

                laatste_ip = :ip

            WHERE gebruiker_id = :id
        ");

        $stmt->execute([

            'id' => $id,

            'ip' => $_SERVER['REMOTE_ADDR'] ?? null

        ]);
    }

    protected function map(
        array $row
    ): User {

        return new User(

            gebruikerId: (int)$row['gebruiker_id'],

            lidId: (int)$row['lid_id'],

            email: $row['email'],

            role: $row['rol'],

            actief: (bool)$row['actief'],

            voornaam: $row['voornaam'],

            achternaam: $row['achternaam'],

            laatsteLogin: $row['laatste_login'],

            laatsteIp: $row['laatste_ip']

        );
    }
}