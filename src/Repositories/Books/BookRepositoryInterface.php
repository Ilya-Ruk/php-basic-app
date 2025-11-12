<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Repositories\Books;

use Rukavishnikov\Php\Basic\App\Models\Books\Book;

interface BookRepositoryInterface
{
    /**
     * @param int $id
     * @return Book
     */
    public function getById(int $id): Book;

    /**
     * @return Book[]
     */
    public function getAll(): array;

    /**
     * @param Book $book
     * @return int
     */
    public function add(Book $book): int;

    /**
     * @param int $id
     * @param Book $book
     * @return void
     */
    public function edit(int $id, Book $book): void;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}
