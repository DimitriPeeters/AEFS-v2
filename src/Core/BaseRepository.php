<?php

declare(strict_types=1);

namespace AEFS\Core;

use PDO;
use RuntimeException;

abstract class BaseRepository
{
    protected Database $database;

    protected string $table;

    protected string $primaryKey = 'id';

    public function __construct(
        Database $database
    ) {
        $this->database = $database;
    }

    public function all(
        string $orderBy = '',
        string $direction = 'ASC'
    ): array {
        $sql = "SELECT * FROM {$this->table}";

        if ($orderBy !== '') {
            $direction = strtoupper($direction);

            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }

            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        $stmt = $this->database->query($sql);

        return array_map(
            [$this, 'map'],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): mixed
    {
        $stmt = $this->database->prepare("
            SELECT *
            FROM {$this->table}
            WHERE {$this->primaryKey} = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->map($row) : null;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->database->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE {$this->primaryKey} = :id
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function delete(int $id): void
    {
        $stmt = $this->database->prepare("
            DELETE
            FROM {$this->table}
            WHERE {$this->primaryKey} = :id
        ");

        $stmt->execute([
            'id' => $id,
        ]);
    }

    public function count(): int
    {
        $stmt = $this->database->query("
            SELECT COUNT(*)
            FROM {$this->table}
        ");

        return (int) $stmt->fetchColumn();
    }

    protected function fetchAll(
        string $sql,
        array $params = []
    ): array {
        $stmt = $this->database->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetch(
        string $sql,
        array $params = []
    ): ?array {
        $stmt = $this->database->prepare($sql);

        $stmt->execute($params);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    protected function execute(
        string $sql,
        array $params = []
    ): bool {
        $stmt = $this->database->prepare($sql);

        return $stmt->execute($params);
    }

    protected function lastInsertId(): int
    {
        return (int) $this->database
            ->pdo()
            ->lastInsertId();
    }

    /**
     * @throws RuntimeException
     */
    abstract protected function map(array $row): mixed;
}