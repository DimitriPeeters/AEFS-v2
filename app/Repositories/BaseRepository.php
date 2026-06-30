<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct(
        protected Database $database
    ) {
        $this->pdo = $database->pdo();
    }

    protected function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    protected function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }
}