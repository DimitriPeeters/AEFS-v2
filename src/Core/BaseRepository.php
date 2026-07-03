<?php

declare(strict_types=1);

namespace AEFS\Core;

use PDO;

abstract class BaseRepository
{
    protected Database $database;

    protected string $table;

    protected string $primaryKey = 'id';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    abstract protected function map(array $row): object;

    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($orderBy !== '') {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->database->query($sql);

        return array_map(
            fn(array $row) => $this->map($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?object
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

    protected function lastInsertId(): int
    {
        return (int)$this->database
            ->pdo()
            ->lastInsertId();
    }

    protected function query(string $sql)
    {
        return $this->database->query($sql);
    }

    protected function prepare(string $sql)
    {
        return $this->database->prepare($sql);
    }
}