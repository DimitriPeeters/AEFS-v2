<?php

declare(strict_types=1);

namespace AEFS\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private PDO $pdo;

    public function __construct(Config $config)
    {
        $host = $config->get('database.host', 'localhost');
        $port = $config->get('database.port', '3306');
        $database = $config->get('database.database');
        $username = $config->get('database.username');
        $password = $config->get('database.password');
        $charset = $config->get('database.charset', 'utf8mb4');

        if (!$database || !$username) {
            throw new RuntimeException('Database configuration is incomplete.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: ' . $exception->getMessage());
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}