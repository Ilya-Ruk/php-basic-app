<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Models\Books;

use Rukavishnikov\Php\Basic\App\Types\Books\BookAuthor;
use Rukavishnikov\Php\Basic\App\Types\Books\BookTitle;
use Rukavishnikov\Php\Basic\App\Types\Books\BookYear;

final class Book
{
    /**
     * @param BookAuthor $author
     * @param BookTitle $title
     * @param BookYear $year
     */
    public function __construct(
        private BookAuthor $author,
        private BookTitle $title,
        private BookYear $year,
    ) {
    }

    /**
     * @return BookAuthor
     */
    public function getAuthor(): BookAuthor
    {
        return $this->author;
    }

    /**
     * @return BookTitle
     */
    public function getTitle(): BookTitle
    {
        return $this->title;
    }

    /**
     * @return BookYear
     */
    public function getYear(): BookYear
    {
        return $this->year;
    }

    /**
     * @return array
     */
    public function getAsArray(): array
    {
        return [
            'author' => $this->getAuthor()->getValue(),
            'title' => $this->getTitle()->getValue(),
            'year' => $this->getYear()->getValue(),
        ];
    }
}
