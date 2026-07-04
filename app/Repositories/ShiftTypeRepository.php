<?php

declare(strict_types=1);

namespace App\Repositories;

use AEFS\Core\BaseRepository;
use AEFS\Core\Database;
use App\Mappers\ShiftTypeMapper;
use App\Models\ShiftType;

final class ShiftTypeRepository extends BaseRepository
{
    protected string $table = 'shift_types';

    protected string $primaryKey = 'id';

    private ShiftTypeMapper $mapper;

    public function __construct(
        Database $database,
        ShiftTypeMapper $mapper
    ) {
        parent::__construct($database);

        $this->mapper = $mapper;
    }

    protected function map(array $row): ShiftType
    {
        return $this->mapper->map($row);
    }

    /**
     * @return ShiftType[]
     */
    public function active(): array
    {
        return array_map(
            [$this, 'map'],
            $this->fetchAll("
                SELECT *
                FROM shift_types
                WHERE actief = 1
                ORDER BY naam
            ")
        );
    }

    public function findByName(string $naam): ?ShiftType
    {
        $row = $this->fetch("
            SELECT *
            FROM shift_types
            WHERE naam = :naam
            LIMIT 1
        ", [
            'naam' => $naam,
        ]);

        return $row ? $this->map($row) : null;
    }

    public function create(
        string $naam,
        bool $actief = true
    ): int {
        return $this->insert([
            'naam'   => $naam,
            'actief' => $actief ? 1 : 0,
        ]);
    }

    public function updateType(
        int $id,
        string $naam,
        bool $actief
    ): bool {
        return $this->updateById($id, [
            'naam'   => $naam,
            'actief' => $actief ? 1 : 0,
        ]);
    }

    public function activate(int $id): bool
    {
        return $this->updateById($id, [
            'actief' => 1,
        ]);
    }

    public function deactivate(int $id): bool
    {
        return $this->updateById($id, [
            'actief' => 0,
        ]);
    }

    public function existsByName(
        string $naam,
        ?int $excludeId = null
    ): bool {
        $sql = "
            SELECT COUNT(*) aantal
            FROM shift_types
            WHERE naam = :naam
        ";

        $params = [
            'naam' => $naam,
        ];

        if ($excludeId !== null) {
            $sql .= " AND id <> :id";
            $params['id'] = $excludeId;
        }

        $row = $this->fetch($sql, $params);

        return ((int) ($row['aantal'] ?? 0)) > 0;
    }

    /**
     * Geeft standaardtype 'Steward' terug.
     */
    public function getDefault(): ?ShiftType
    {
        return $this->findByName('Steward');
    }

    /**
     * Maakt het standaardtype automatisch aan indien het ontbreekt.
     */
    public function ensureDefault(): ShiftType
    {
        $type = $this->getDefault();

        if ($type !== null) {
            return $type;
        }

        $id = $this->create('Steward');

        return $this->find($id);
    }
}