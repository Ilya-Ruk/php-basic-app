<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Types\Books;

use InvalidArgumentException;

final class BookAuthor
{
    const MIN_LENGTH = 1;
    const MAX_LENGTH = 100;

    /**
     * @param string $author
     */
    public function __construct(private string $author)
    {
        if (strlen($this->author) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(sprintf("Author name length must be greater or equal of %d char!", self::MIN_LENGTH), 400);
        }

        if (strlen($this->author) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(sprintf("Author name must be less or equal of %d chars!", self::MAX_LENGTH), 400);
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->author;
    }
}
