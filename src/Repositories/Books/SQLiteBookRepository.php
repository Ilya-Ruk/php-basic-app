<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Repositories\Books;

use Rukavishnikov\Php\Basic\App\Databases\DatabaseInterface;
use Rukavishnikov\Php\Basic\App\Entities\Books\Book;
use Rukavishnikov\Php\Basic\App\Factories\Books\BookFactory;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookInsertException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookUpdateException;

final class SQLiteBookRepository implements BookRepositoryInterface
{
    /**
     * @var string
     */
    private string $tableName = 'books';

    /**
     * @var string
     */
    private string $primaryKey = 'id';

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
        $rows = $this->database->getByConditions(
            $this->tableName,
            [$this->primaryKey => $id],
            1
        );

        if (count($rows) === 0) {
            throw new BookNotFoundException(sprintf("Book with id %d not found!", $id));
        }

        return BookFactory::createFromDatabaseRow($rows[0]);
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        $rows = $this->database->getByConditions($this->tableName);

        $bookList = [];

        foreach ($rows as $row) {
            $bookList[] = BookFactory::createFromDatabaseRow($row);
        }

        return $bookList;
    }

    /**
     * @inheritDoc
     */
    public function add(Book $book): void
    {
        $data = $book->getAsArray();

        $rowCount = $this->database->insert($this->tableName, $data);

        if ($rowCount !== 1) {
            throw new BookInsertException('Book insert error!');
        }
    }

    /**
     * @inheritDoc
     */
    public function edit(int $id, Book $book): void
    {
        $data = $book->getAsArray();

        $rowCount = $this->database->update(
            $this->tableName,
            $data,
            [$this->primaryKey => $id]
        );

        if ($rowCount !== 1) {
            throw new BookUpdateException(sprintf("Book with id %d update error!", $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        $rowCount = $this->database->delete(
            $this->tableName,
            [$this->primaryKey => $id]
        );

        if ($rowCount !== 1) {
            throw new BookDeleteException(sprintf("Book with id %d delete error!", $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNextId(): int
    {
        return $this->database->getNextId($this->tableName);
    }
}
