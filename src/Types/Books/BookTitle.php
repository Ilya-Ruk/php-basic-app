<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Types\Books;

use InvalidArgumentException;

final class BookTitle
{
    const MIN_LENGTH = 1;
    const MAX_LENGTH = 200;

    /**
     * @param string $title
     */
    public function __construct(private string $title)
    {
        if (strlen($this->title) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(sprintf("Title length must be greater or equal of %d char!", self::MIN_LENGTH), 400);
        }

        if (strlen($this->title) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(sprintf("Title length must be less or equal of %d chars!", self::MAX_LENGTH), 400);
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }
}
