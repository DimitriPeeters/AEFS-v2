<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\Database;
use App\Mappers\MemberMapper;
use App\Models\Member;
use PDO;

final class MemberRepository
{
    private const DEFAULT_ORDER = 'voornaam ASC, achternaam ASC';

    public function __construct(
        private readonly Database $database,
        private readonly MemberMapper $mapper
    ) {
    }

    /**
     * @return Member[]
     */
    public function all(): array
    {
        $statement = $this->database->query(
            '
            SELECT *
            FROM leden
            ORDER BY ' . self::DEFAULT_ORDER
        );

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
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

        $zoekwaarde = '%' . $zoekterm . '%';

        $statement = $this->database->prepare(
            '
            SELECT *
            FROM leden
            WHERE voornaam LIKE :zoek_voornaam
               OR achternaam LIKE :zoek_achternaam
               OR email LIKE :zoek_email
               OR gemeente LIKE :zoek_gemeente
            ORDER BY ' . self::DEFAULT_ORDER
        );

        $statement->execute([
            'zoek_voornaam' => $zoekwaarde,
            'zoek_achternaam' => $zoekwaarde,
            'zoek_email' => $zoekwaarde,
            'zoek_gemeente' => $zoekwaarde,
        ]);

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return Member[]
     */
    public function paginate(
        int $page = 1,
        int $perPage = 25
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $offset = ($page - 1) * $perPage;

        $statement = $this->database->prepare(
            '
            SELECT *
            FROM leden
            ORDER BY ' . self::DEFAULT_ORDER . '
            LIMIT :offset, :limit
            '
        );

        $statement->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $statement->bindValue(
            ':limit',
            $perPage,
            PDO::PARAM_INT
        );

        $statement->execute();

        return $this->mapRows(
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function count(): int
    {
        $statement = $this->database->query(
            '
            SELECT COUNT(*)
            FROM leden
            '
        );

        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?Member
    {
        $statement = $this->database->prepare(
            '
            SELECT *
            FROM leden
            WHERE lid_id = :id
            LIMIT 1
            '
        );

        $statement->execute([
            'id' => $id,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapper->fromDatabase($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $data = $this->mapper->toDatabase($data);

        $statement = $this->database->prepare(
            '
            INSERT INTO leden
            (
                voornaam,
                achternaam,
                email,
                telefoon,
                straat,
                postcode,
                gemeente,
                land,
                geslacht,
                geboortedatum,
                rekeningnummer,
                rijksregisternummer,
                tshirtmaat,
                opmerkingen,
                actief,
                gdpr_consent,
                gdpr_timestamp,
                aangemaakt_op,
                bijgewerkt_op
            )
            VALUES
            (
                :voornaam,
                :achternaam,
                :email,
                :telefoon,
                :straat,
                :postcode,
                :gemeente,
                :land,
                :geslacht,
                :geboortedatum,
                :rekeningnummer,
                :rijksregisternummer,
                :tshirtmaat,
                :opmerkingen,
                :actief,
                :gdpr_consent,
                :gdpr_timestamp,
                NOW(),
                NOW()
            )
            '
        );

        $statement->execute($data);

        return $this->database->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(
        int $id,
        array $data
    ): void {
        $data = $this->mapper->toDatabase($data);
        $data['lid_id'] = $id;

        $statement = $this->database->prepare(
            '
            UPDATE leden
            SET
                voornaam = :voornaam,
                achternaam = :achternaam,
                email = :email,
                telefoon = :telefoon,
                straat = :straat,
                postcode = :postcode,
                gemeente = :gemeente,
                land = :land,
                geslacht = :geslacht,
                geboortedatum = :geboortedatum,
                rekeningnummer = :rekeningnummer,
                rijksregisternummer = :rijksregisternummer,
                tshirtmaat = :tshirtmaat,
                opmerkingen = :opmerkingen,
                actief = :actief,
                gdpr_consent = :gdpr_consent,
                gdpr_timestamp = :gdpr_timestamp,
                bijgewerkt_op = NOW()
            WHERE lid_id = :lid_id
            '
        );

        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare(
            '
            DELETE FROM leden
            WHERE lid_id = :id
            '
        );

        $statement->execute([
            'id' => $id,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return Member[]
     */
    private function mapRows(array $rows): array
    {
        return array_map(
            fn (array $row): Member => $this->mapper->fromDatabase($row),
            $rows
        );
    }
}