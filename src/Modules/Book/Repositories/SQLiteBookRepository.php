<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Repositories;

use Rukavishnikov\Php\Basic\App\Databases\DatabaseInterface;
use Rukavishnikov\Php\Basic\App\Modules\Book\Entities\Book;
use Rukavishnikov\Php\Basic\App\Modules\Book\Factories\BookFactory;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookAddException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookEditException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetAllException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetNextIdException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookNotFoundException;
use RuntimeException;

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
        try {
            $rows = $this->database->getByConditions(
                $this->tableName,
                [$this->primaryKey => $id],
                1
            );
        } catch (RuntimeException $e) {
            throw new BookGetException(sprintf("Book with id %d get error!", $id), 0, $e);
        }

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
        try {
            $rows = $this->database->getByConditions($this->tableName);
        } catch (RuntimeException $e) {
            throw new BookGetAllException('Get all books error!', 0, $e);
        }

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

        try {
            $rowCount = $this->database->insert($this->tableName, $data);
        } catch (RuntimeException $e) {
            throw new BookAddException('Book add error!', 0, $e);
        }

        if ($rowCount !== 1) {
            throw new BookAddException('Book add error!');
        }
    }

    /**
     * @inheritDoc
     */
    public function edit(int $id, Book $book): void
    {
        $data = $book->getAsArray();

        try {
            $rowCount = $this->database->update(
                $this->tableName,
                $data,
                [$this->primaryKey => $id]
            );
        } catch (RuntimeException $e) {
            throw new BookEditException(sprintf("Book with id %d edit error!", $id), 0, $e);
        }

        if ($rowCount !== 1) {
            throw new BookEditException(sprintf("Book with id %d edit error!", $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        try {
            $rowCount = $this->database->delete(
                $this->tableName,
                [$this->primaryKey => $id]
            );
        } catch (RuntimeException $e) {
            throw new BookDeleteException(sprintf("Book with id %d delete error!", $id), 0, $e);
        }

        if ($rowCount !== 1) {
            throw new BookDeleteException(sprintf("Book with id %d delete error!", $id));
        }
    }

    /**
     * @inheritDoc
     */
    public function getNextId(): int
    {
        try {
            return $this->database->getNextId($this->tableName);
        } catch (RuntimeException $e) {
            throw new BookGetNextIdException('Get book next id error!', 0, $e);
        }
    }
}
