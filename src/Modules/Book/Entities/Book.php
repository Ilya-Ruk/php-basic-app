<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Entities;

use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookAuthor;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookId;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookTitle;
use Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects\BookYear;

final class Book
{
    /**
     * @param BookId|null $id
     * @param BookAuthor $author
     * @param BookTitle $title
     * @param BookYear $year
     */
    public function __construct(
        private ?BookId $id,
        private BookAuthor $author,
        private BookTitle $title,
        private BookYear $year,
    ) {
    }

    /**
     * @return BookId|null
     */
    public function getId(): ?BookId
    {
        return $this->id;
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
     * @param int $id
     * @return Book
     */
    public function withId(int $id): Book
    {
        $new = clone $this;

        $new->id = BookId::createFromInt($id);

        return $new;
    }

    /**
     * @return Book
     */
    public function withoutId(): Book
    {
        $new = clone $this;

        $new->id = null;

        return $new;
    }

    /**
     * @return array
     */
    public function getAsArray(): array
    {
        return [
            'id' => $this->getId()?->getValue(),
            'author' => $this->getAuthor()->getValue(),
            'title' => $this->getTitle()->getValue(),
            'year' => $this->getYear()->getValue(),
        ];
    }
}
