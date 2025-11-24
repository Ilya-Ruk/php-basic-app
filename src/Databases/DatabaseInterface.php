<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Databases;

use InvalidArgumentException;
use Rukavishnikov\Php\Basic\App\Databases\Exceptions\DatabaseException;

interface DatabaseInterface
{
    /**
     * @param string $tableName
     * @param array $conditions
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     * @throws DatabaseException
     */
    public function getByConditions(string $tableName, array $conditions = [], ?int $limit = null, ?int $offset = null): array;

    /**
     * @param string $tableName
     * @param array $data
     * @return int
     * @throws InvalidArgumentException
     * @throws DatabaseException
     */
    public function insert(string $tableName, array $data): int;

    /**
     * @param string $tableName
     * @param array $data
     * @param array $conditions
     * @return int
     * @throws InvalidArgumentException
     * @throws DatabaseException
     */
    public function update(string $tableName, array $data, array $conditions): int;

    /**
     * @param string $tableName
     * @param array $conditions
     * @return int
     * @throws DatabaseException
     */
    public function delete(string $tableName, array $conditions): int;

    /**
     * @param string $tableName
     * @return array
     * @throws DatabaseException
     */
    public function getPrimaryKey(string $tableName): array;

    /**
     * @param string $tableName
     * @return int
     * @throws DatabaseException
     */
    public function getNextId(string $tableName): int;
}
