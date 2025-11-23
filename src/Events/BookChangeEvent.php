<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Events;

use Rukavishnikov\Php\Basic\App\Entities\Books\Book;

final class BookChangeEvent
{
    /**
     * @param Book|null $oldBook
     * @param Book|null $newBook
     */
    public function __construct(
        public ?Book $oldBook,
        public ?Book $newBook,
    ) {
    }
}
