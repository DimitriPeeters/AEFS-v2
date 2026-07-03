<?php

declare(strict_types=1);

namespace AEFS\Repositories;

use AEFS\Core\BaseRepository;

final class HomeRepository extends BaseRepository
{
    public function databaseVersion(): string
    {
        return (string) $this->database
            ->query("SELECT VERSION()")
            ->fetchColumn();
    }

    protected function map(array $row): object
    {
        return (object) $row;
    }
}