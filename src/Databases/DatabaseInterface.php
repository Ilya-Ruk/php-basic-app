<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Databases;

interface DatabaseInterface
{
    /**
     * @param string $tableName
     * @param array $conditions
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getByConditions(string $tableName, array $conditions = [], ?int $limit = null, ?int $offset = null): array;

    /**
     * @param string $tableName
     * @param array $data
     * @return int
     */
    public function insert(string $tableName, array $data): int;

    /**
     * @param string $tableName
     * @param array $data
     * @param array $conditions
     * @return int
     */
    public function update(string $tableName, array $data, array $conditions): int;

    /**
     * @param string $tableName
     * @param array $conditions
     * @return int
     */
    public function delete(string $tableName, array $conditions): int;

    /**
     * @param string $tableName
     * @return array
     */
    public function getPrimaryKey(string $tableName): array;

    /**
     * @param string $tableName
     * @return int
     */
    public function getNextId(string $tableName): int;
}
