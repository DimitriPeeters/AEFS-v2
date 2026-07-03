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

    public function all(): array
    {
        $stmt = $this->database->pdo()->query("
            SELECT *
            FROM gebruikers
            ORDER BY email
        ");

        return array_map(
            fn(array $row) => $this->map($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function search(string $zoekterm): array
    {
        $stmt = $this->database->prepare("
            SELECT *
            FROM gebruikers
            WHERE
                email LIKE :zoek
                OR rol LIKE :zoek
            ORDER BY email
        ");

        $stmt->execute([
            'zoek' => '%' . $zoekterm . '%'
        ]);

        return array_map(
            fn(array $row) => $this->map($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?User
    {
        $stmt = $this->database->prepare("
            SELECT *
            FROM gebruikers
            WHERE gebruiker_id=:id
        ");

        $stmt->execute([
            'id'=>$id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->map($row) : null;
    }

    public function findByEmail(string $email): ?User
{
    $stmt = $this->database->prepare("
        SELECT *
        FROM gebruikers
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        'email' => trim($email)
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $this->map($row) : null;
}

    public function create(array $data): int
    {
        $stmt = $this->database->prepare("
            INSERT INTO gebruikers
            (
                lid_id,
                email,
                wachtwoord_hash,
                rol,
                actief,
                mail_blacklist,
                wachtwoord_moet_wijzigen
            )
            VALUES
            (
                :lid_id,
                :email,
                :wachtwoord_hash,
                :rol,
                :actief,
                :mail_blacklist,
                :wachtwoord_moet_wijzigen
            )
        ");

        $stmt->execute([
            'lid_id'=>$data['lid_id'],
            'email'=>$data['email'],
            'wachtwoord_hash'=>password_hash($data['password'], PASSWORD_DEFAULT),
            'rol'=>$data['rol'],
            'actief'=>$data['actief'],
            'mail_blacklist'=>$data['mail_blacklist'],
            'wachtwoord_moet_wijzigen'=>$data['wachtwoord_moet_wijzigen']
        ]);

        return (int)$this->database->pdo()->lastInsertId();
    }

    public function update(int $id,array $data): void
    {
        $sql="
            UPDATE gebruikers
            SET
                lid_id=:lid_id,
                email=:email,
                rol=:rol,
                actief=:actief,
                mail_blacklist=:mail_blacklist,
                wachtwoord_moet_wijzigen=:wachtwoord_moet_wijzigen
        ";

        $params=[
            'lid_id'=>$data['lid_id'],
            'email'=>$data['email'],
            'rol'=>$data['rol'],
            'actief'=>isset($data['actief']) ? 1 : 0,
            'mail_blacklist'=>isset($data['mail_blacklist']) ? 1 : 0,
            'wachtwoord_moet_wijzigen'=>isset($data['wachtwoord_moet_wijzigen']) ? 1 : 0,
            'id'=>$id
        ];

        if(!empty($data['password'])){

            $sql.=", wachtwoord_hash=:wachtwoord_hash";

            $params['wachtwoord_hash']=password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            );
        }

        $sql.=" WHERE gebruiker_id=:id";

        $stmt=$this->database->prepare($sql);

        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt=$this->database->prepare("
            DELETE FROM gebruikers
            WHERE gebruiker_id=:id
        ");

        $stmt->execute([
            'id'=>$id
        ]);
    }

    private function map(array $row): User
    {
        return new User(

            gebruikerId:(int)$row['gebruiker_id'],

            lidId:(int)$row['lid_id'],

            email:$row['email'],

            wachtwoordHash:$row['wachtwoord_hash'],

            rol:$row['rol'],

            actief:(bool)$row['actief'],

            mailBlacklist:(bool)$row['mail_blacklist'],

            wachtwoordMoetWijzigen:(bool)$row['wachtwoord_moet_wijzigen'],

            resetToken:$row['reset_token'],

            resetTokenExpires:$row['reset_token_expires']

        );
    }
}