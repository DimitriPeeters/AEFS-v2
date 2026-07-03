<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\Database;
use AEFS\Mappers\MemberMapper;
use AEFS\Models\Member;
use PDO;

final class MemberRepository
{
    private const DEFAULT_ORDER = 'voornaam ASC, achternaam ASC';

    public function __construct(
        private Database $database,
        private MemberMapper $mapper
    ) {
    }

    /**
     * @return Member[]
     */
    public function all(): array
    {
        $stmt = $this->database->query("
            SELECT *
            FROM leden
            ORDER BY " . self::DEFAULT_ORDER);

        return array_map(
            fn (array $row) => $this->mapper->fromDatabase($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return Member[]
     */
    public function search(string $zoekterm): array
    {
        $zoek = '%' . trim($zoekterm) . '%';

        $stmt = $this->database->prepare("
            SELECT *
            FROM leden
            WHERE
                voornaam LIKE :zoek
                OR achternaam LIKE :zoek
                OR email LIKE :zoek
                OR gemeente LIKE :zoek
            ORDER BY " . self::DEFAULT_ORDER);

        $stmt->execute([
            'zoek' => $zoek,
        ]);

        return array_map(
            fn (array $row) => $this->mapper->fromDatabase($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
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

        $offset = ($page - 1) * $perPage;

        $stmt = $this->database->prepare("
            SELECT *
            FROM leden
            ORDER BY " . self::DEFAULT_ORDER . "
            LIMIT :offset, :limit
        ");

        $stmt->bindValue(
            'offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            'limit',
            $perPage,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return array_map(
            fn (array $row) => $this->mapper->fromDatabase($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function count(): int
    {
        $stmt = $this->database->query("
            SELECT COUNT(*)
            FROM leden
        ");

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?Member
    {
        $stmt = $this->database->prepare("
            SELECT *
            FROM leden
            WHERE lid_id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapper->fromDatabase($row);
    }

    public function create(array $data): int
    {
        $data = $this->mapper->toDatabase($data);

        $stmt = $this->database->prepare("
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
        ");

        $stmt->execute($data);

        return (int) $this->database
            ->pdo()
            ->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): void {

        $data = $this->mapper->toDatabase($data);

        $data['lid_id'] = $id;

        $stmt = $this->database->prepare("
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
        ");

        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->database->prepare("
            DELETE
            FROM leden
            WHERE lid_id = :id
        ");

        $stmt->execute([
            'id' => $id,
        ]);
    }
}