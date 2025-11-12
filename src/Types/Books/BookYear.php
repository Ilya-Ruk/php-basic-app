<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Types\Books;

use InvalidArgumentException;

final class BookYear
{
    const MIN_YEAR = 1455;

    /**
     * @param int $year
     */
    public function __construct(private int $year)
    {
        if ($this->year < self::MIN_YEAR) {
            throw new InvalidArgumentException(sprintf("Year must be greater or equal of %d!", self::MIN_YEAR), 400);
        }

        $maxYear = (int)date('Y') + 1;

        if ($this->year > $maxYear) {
            throw new InvalidArgumentException(sprintf("Year must be less or equal of %d!", $maxYear), 400);
        }
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->year;
    }
}
