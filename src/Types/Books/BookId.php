<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Types\Books;

use InvalidArgumentException;

final class BookId
{
    const MIN_ID = 1;

    /**
     * @param int $id
     */
    private function __construct(private int $id)
    {
        if ($this->id < self::MIN_ID) {
            throw new InvalidArgumentException(sprintf("Id must be greater or equal of %d!", self::MIN_ID), 400);
        }
    }

    /**
     * @param int $id
     * @return BookId
     */
    public static function createFromInt(int $id): BookId
    {
        return new BookId($id);
    }

    /**
     * @param string $id
     * @return BookId
     */
    public static function createFromString(string $id): BookId
    {
        return new BookId((int)$id);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->id;
    }
}
