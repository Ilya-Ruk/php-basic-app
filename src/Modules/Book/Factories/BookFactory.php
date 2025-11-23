<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Factories;

use Rukavishnikov\Php\Basic\App\Modules\Book\Entities\Book;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookAuthor;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookId;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookTitle;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookYear;

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
