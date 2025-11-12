<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Databases;

interface DatabaseInterface
{
    /**
     * @param string $tableName
     * @param int $id
     * @return array
     */
    public function getByPrimaryKey(string $tableName, int $id): array;

    /**
     * @param string $tableName
     * @return array
     */
    public function getAll(string $tableName): array;

    /**
     * @param string $tableName
     * @param array $data
     * @return int
     */
    public function insert(string $tableName, array $data): int;

    /**
     * @param string $tableName
     * @param int $id
     * @param array $data
     * @return void
     */
    public function update(string $tableName, int $id, array $data): void;

    /**
     * @param string $tableName
     * @param int $id
     * @return void
     */
    public function delete(string $tableName, int $id): void;

    /**
     * @param string $tableName
     * @return string|null
     */
    public function getPrimaryKey(string $tableName): ?string;
}
