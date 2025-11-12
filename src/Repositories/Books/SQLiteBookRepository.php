<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Repositories\Books;

use Rukavishnikov\Php\Basic\App\Databases\DatabaseInterface;
use Rukavishnikov\Php\Basic\App\Databases\RecordNotFoundException;
use Rukavishnikov\Php\Basic\App\Models\Books\Book;
use Rukavishnikov\Php\Basic\App\Models\Books\BookFactory;

final class SQLiteBookRepository implements BookRepositoryInterface
{
    /**
     * @var string
     */
    private string $tableName = 'books';

    /**
     * @param DatabaseInterface $database
     */
    public function __construct(
        private DatabaseInterface $database,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): Book
    {
        try {
            $row = $this->database->getByPrimaryKey($this->tableName, $id);
        } catch (RecordNotFoundException $e) {
            throw new BookNotFoundException(sprintf("Book with id %d not found!", $id), 404, $e);
        }

        return BookFactory::createFromArray($row);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $rows = $this->database->getAll($this->tableName);

        $primaryKey = $this->database->getPrimaryKey($this->tableName);

        $bookList = [];

        foreach ($rows as $row) {
            $id = $row[$primaryKey];

            $bookList[$id] = BookFactory::createFromArray($row);
        }

        return $bookList;
    }

    /**
     * @inheritDoc
     */
    public function add(Book $book): int
    {
        $data = $book->getAsArray();

        return $this->database->insert($this->tableName, $data);
    }

    /**
     * @inheritDoc
     */
    public function edit(int $id, Book $book): void
    {
        $data = $book->getAsArray();

        $this->database->update($this->tableName, $id, $data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        $this->database->delete($this->tableName, $id);
    }
}
