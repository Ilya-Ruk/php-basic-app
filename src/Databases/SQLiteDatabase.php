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
            throw new RuntimeException(sprintf("Database '%s' connect error!", $dbFileName), 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getByConditions(string $tableName, array $conditions = [], ?int $limit = null, ?int $offset = null): array
    {
        $whereList = [];
        $paramList = [];

        foreach ($conditions as $field => $value) {
            $whereList[] = '`' . $field . '` = :' . $field;
            $paramList[$field] = $value;
        }

        $query = 'SELECT *';
        $query .= ' FROM `' . $tableName . '`';

        if (count($whereList) > 0) {
            $query .= ' WHERE ' . implode(', ', $whereList);
        }

        if (!is_null($limit)) {
            $query .= ' LIMIT ' . $limit;
        }

        if (!is_null($offset)) {
            $query .= ' OFFSET ' . $offset;
        }

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, $paramList);

        return $this->fetchAll($statement);
    }

    /**
     * @inheritDoc
     */
    public function insert(string $tableName, array $data): int
    {
        if (count($data) === 0) {
            throw new RuntimeException("Empty data for insert!");
        }

        $fieldList = [];
        $valueList = [];
        $paramList = [];

        foreach ($data as $field => $value) {
            $fieldList[] = '`' . $field . '`';
            $valueList[] = ':' . $field;
            $paramList[$field] = $value;
        }

        $query = 'INSERT INTO `' . $tableName . '`';
        $query .= ' (' . implode(', ', $fieldList) . ')';
        $query .= ' VALUES (' . implode(', ', $valueList) . ')';

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, $paramList);

        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function update(string $tableName, array $data, array $conditions): int
    {
        if (count($data) === 0) {
            throw new RuntimeException("Empty data for update!");
        }

        $setList = [];
        $whereList = [];
        $paramList = [];

        foreach ($data as $field => $value) {
            if (array_key_exists($field, $conditions)) {
                continue;
            }

            $setList[] = '`' . $field . '` = :' . $field;
            $paramList[$field] = $value;
        }

        foreach ($conditions as $field => $value) {
            $whereList[] = '`' . $field . '` = :' . $field;
            $paramList[$field] = $value;
        }

        $query = 'UPDATE `' . $tableName . '`';
        $query .= ' SET ' . implode(', ', $setList);

        if (count($whereList) > 0) {
            $query .= ' WHERE ' . implode(', ', $whereList);
        }

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, $paramList);

        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $tableName, array $conditions): int
    {
        $whereList = [];
        $paramList = [];

        foreach ($conditions as $field => $value) {
            $whereList[] = '`' . $field . '` = :' . $field;
            $paramList[$field] = $value;
        }

        $query = 'DELETE FROM `' . $tableName . '`';

        if (count($whereList) > 0) {
            $query .= ' WHERE ' . implode(', ', $whereList);
        }

        $statement = $this->prepareQuery($query);
        $this->executePreparedQuery($statement, $paramList);

        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function getPrimaryKey(string $tableName): array
    {
        $query = 'PRAGMA table_info (`' . $tableName . '`)';

        $statement = $this->executeQuery($query);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $fieldList = [];

        foreach ($rows as $row) {
            if ($row['pk'] === 1) {
                $fieldList[] = $row['name'];
            }
        }

        return $fieldList;
    }

    /**
     * @inheritDoc
     */
    public function getNextId(string $tableName): int
    {
        $this->connection->beginTransaction();

        $rows = $this->getByConditions(
            'sqlite_sequence',
            ['name' => $tableName],
            1
        );

        if (count($rows) === 0) {
            $nextId = 1;

            $rowCount = $this->insert(
                'sqlite_sequence',
                [
                    'name' => $tableName,
                    'seq' => $nextId,
                ]
            );
        } else {
            $nextId = $rows[0]['seq'] + 1;

            $rowCount = $this->update(
                'sqlite_sequence',
                ['seq' => $nextId],
                ['name' => $tableName]
            );
        }

        if ($rowCount !== 1) {
            $this->connection->rollBack();

            throw new RuntimeException("Table 'sqlite_sequence' insert/update error!");
        }

        $this->connection->commit();

        return $nextId;
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
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new RuntimeException(sprintf("Database query '%s' prepare error!", $query), 0, $e);
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
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new RuntimeException(sprintf("Database query '%s' execute error!", $statement->queryString), 0, $e);
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
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new RuntimeException(sprintf("Database query '%s' execute error!", $query), 0, $e);
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
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw new RuntimeException(sprintf("Database query '%s' fetch error!", $statement->queryString), 0, $e);
        }

        return $rows;
    }
}
