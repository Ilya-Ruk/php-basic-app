<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Factories\Books;

use Rukavishnikov\Php\Basic\App\Entities\Books\Book;
use Rukavishnikov\Php\Basic\App\Types\Books\BookAuthor;
use Rukavishnikov\Php\Basic\App\Types\Books\BookId;
use Rukavishnikov\Php\Basic\App\Types\Books\BookTitle;
use Rukavishnikov\Php\Basic\App\Types\Books\BookYear;

final class BookFactory
{
    /**
     * @param array $data
     * @return Book
     */
    public static function createFromRequestData(array $data): Book
    {
        return new Book(
            null,
            BookAuthor::createFromString($data['Author'] ?? ''),
            BookTitle::createFromString($data['Title'] ?? ''),
            BookYear::createFromInt($data['Year'] ?? 0)
        );
    }

    /**
     * @param array $row
     * @return Book
     */
    public static function createFromDatabaseRow(array $row): Book
    {
        return new Book(
            BookId::createFromInt($row['id'] ?? 0),
            BookAuthor::createFromString($row['author'] ?? ''),
            BookTitle::createFromString($row['title'] ?? ''),
            BookYear::createFromInt($row['year'] ?? 0)
        );
    }
}
