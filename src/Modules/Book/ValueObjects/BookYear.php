<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\ValueObjects;

use InvalidArgumentException;

final class BookYear
{
    const MIN_YEAR = 1455;

    /**
     * @param int $year
     */
    private function __construct(private int $year)
    {
        if ($this->year < self::MIN_YEAR) {
            throw new InvalidArgumentException(sprintf("Year must be greater or equal of %d!", self::MIN_YEAR));
        }

        $maxYear = (int)date('Y') + 1;

        if ($this->year > $maxYear) {
            throw new InvalidArgumentException(sprintf("Year must be less or equal of %d!", $maxYear));
        }
    }

    /**
     * @param int $year
     * @return BookYear
     */
    public static function createFromInt(int $year): BookYear
    {
        return new BookYear($year);
    }

    /**
     * @param string $year
     * @return BookYear
     */
    public static function createFromString(string $year): BookYear
    {
        return new BookYear((int)$year);
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
