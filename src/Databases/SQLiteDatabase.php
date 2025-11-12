<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Databases;

use PDO;
use PDOException;
use PDOStatement;
use Rukavishnikov\Php\Helper\Classes\FilePath;
use RuntimeException;

final class SQLiteDatabase implements DatabaseInterface
{
    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param FilePath $dbFilePath
     */
    public function __construct(
        private FilePath $dbFilePath,
    ) {
        $dbFileName = $this->dbFilePath->getFilePath();

        try {
            $this->connection = new PDO('sqlite:' . $dbFileName);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Database '%s' connect error!", $dbFileName), 500, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getByPrimaryKey(string $tableName, int $id): array
    {
        $primaryKey = $this->getPrimaryKey($tableName);

        $query = 'SELECT *';
        $query .= ' FROM `' . $tableName . '`';
        $query .= ' WHERE `' . $primaryKey . '` = :' . $primaryKey;
        $query .= ' LIMIT 1';

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, [$primaryKey => $id]);
        $rows = $this->fetchAll($statement);

        if (count($rows) === 0) {
            throw new RecordNotFoundException(sprintf("Record with id %d not found!", $id), 404);
        }

        return $rows[0];
    }

    /**
     * @inheritDoc
     */
    public function getAll(string $tableName): array
    {
        $query = 'SELECT *';
        $query .= ' FROM `' . $tableName . '`';

        $statement = $this->executeQuery($query);

        return $this->fetchAll($statement);
    }

    /**
     * @inheritDoc
     */
    public function insert(string $tableName, array $data): int
    {
        if (count($data) === 0) {
            throw new RuntimeException("Empty data for insert!", 500);
        }

        $fieldList = array_keys($data);

        $valueList = array_map(
            function (int|string $field) {
                return ':' . $field;
            },
            $fieldList
        );

        $query = 'INSERT INTO `' . $tableName . '`';
        $query .= ' (`' . implode('`, `', $fieldList) . '`)';
        $query .= ' VALUES (' . implode(', ', $valueList) . ')';

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, $data);

        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritDoc
     */
    public function update(string $tableName, int $id, array $data): void
    {
        if (count($data) === 0) {
            throw new RuntimeException("Empty data for update!", 500);
        }

        $primaryKey = $this->getPrimaryKey($tableName);

        $fieldList = array_keys($data);

        $setList = array_map(
            function (int|string $field) {
                return '`' . $field . '` = :' . $field;
            },
            $fieldList
        );

        $query = 'UPDATE `' . $tableName . '`';
        $query .= ' SET ' . implode(', ', $setList);
        $query .= ' WHERE `' . $primaryKey . '` = :' . $primaryKey;

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, array_merge($data, [$primaryKey => $id]));

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException(sprintf("Update record with id %d error!", $id), 500);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(string $tableName, int $id): void
    {
        $primaryKeyName = $this->getPrimaryKey($tableName);

        $query = 'DELETE FROM `' . $tableName . '`';
        $query .= ' WHERE `' . $primaryKeyName . '` = :' . $primaryKeyName;

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, [$primaryKeyName => $id]);

        if ($statement->rowCount() !== 1) {
            throw new RuntimeException(sprintf("Delete record with id %d error!", $id), 500);
        }
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryKey(string $tableName): ?string
    {
        $query = 'PRAGMA table_info (`' . $tableName . '`)';

        $statement = $this->executeQuery($query);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if ($row['pk'] === 1) {
                return $row['name'];
            }
        }

        return null;
    }

    /**
     * @param string $query
     * @return PDOStatement
     */
    private function prepareQuery(string $query): PDOStatement
    {
        try {
            $statement = $this->connection->prepare($query);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Database query '%s' prepare error!", $query), 500, $e);
        }

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     * @param array $params
     * @return void
     */
    private function executePreparedQuery(PDOStatement $statement, array $params): void
    {
        try {
            $statement->execute($params);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Database query '%s' execute error!", $statement->queryString), 500, $e);
        }
    }

    /**
     * @param string $query
     * @return PDOStatement
     */
    private function executeQuery(string $query): PDOStatement
    {
        try {
            $statement = $this->connection->query($query);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Database query '%s' execute error!", $query), 500, $e);
        }

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     * @return array
     */
    private function fetchAll(PDOStatement $statement): array
    {
        try {
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Database query '%s' fetch error!", $statement->queryString), 500, $e);
        }

        return $rows;
    }
}
