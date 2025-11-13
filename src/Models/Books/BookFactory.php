<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Models\Books;

use Rukavishnikov\Php\Basic\App\Types\Books\BookAuthor;
use Rukavishnikov\Php\Basic\App\Types\Books\BookTitle;
use Rukavishnikov\Php\Basic\App\Types\Books\BookYear;

final class BookFactory
{
    /**
     * @param array $data
     * @return Book
     */
    public static function createFromArray(array $data): Book
    {
        $data = array_change_key_case($data);

        return new Book(
            new BookAuthor($data['author'] ?? ''),
            new BookTitle($data['title'] ?? ''),
            new BookYear((int)($data['year'] ?? 0))
        );
    }
}
