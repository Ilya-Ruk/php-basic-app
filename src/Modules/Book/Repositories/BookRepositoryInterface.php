<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Repositories;

use Rukavishnikov\Php\Basic\App\Modules\Book\Entities\Book;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookAddException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookDeleteException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookEditException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetAllException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookGetNextIdException;
use Rukavishnikov\Php\Basic\App\Modules\Book\Repositories\Exceptions\BookNotFoundException;

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
