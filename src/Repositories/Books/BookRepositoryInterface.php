<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Repositories\Books;

use Rukavishnikov\Php\Basic\App\Entities\Books\Book;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookAddException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetAllException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookGetNextIdException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookNotFoundException;
use Rukavishnikov\Php\Basic\App\Repositories\Books\Exceptions\BookEditException;

interface BookRepositoryInterface
{
    /**
     * @param int $id
     * @return Book
     * @throws BookGetException
     * @throws BookNotFoundException
     */
    public function getById(int $id): Book;

    /**
     * @return Book[]
     * @throws BookGetAllException
     */
    public function getAll(): array;

    /**
     * @param Book $book
     * @return void
     * @throws BookAddException
     */
    public function add(Book $book): void;

    /**
     * @param int $id
     * @param Book $book
     * @return void
     * @throws BookEditException
     */
    public function edit(int $id, Book $book): void;

    /**
     * @param int $id
     * @return void
     * @throws BookDeleteException
     */
    public function delete(int $id): void;

    /**
     * @return int
     * @throws BookGetNextIdException
     */
    public function getNextId(): int;
}
