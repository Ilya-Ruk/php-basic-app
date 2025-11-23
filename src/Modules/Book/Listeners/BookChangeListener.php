<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Modules\Book\Listeners;

use Psr\Log\LoggerInterface;
use Rukavishnikov\Php\Basic\App\Modules\Book\Events\BookChangeEvent;
use Rukavishnikov\Php\Helper\Classes\ValueToStringHelper;

final class BookChangeListener
{
    /**
     * @param LoggerInterface $logger
     * @param ValueToStringHelper $valueToStringHelper
     */
    public function __construct(
        private LoggerInterface $logger,
        private ValueToStringHelper $valueToStringHelper,
    ) {
    }

    /**
     * @param BookChangeEvent $event
     * @return void
     */
    public function __invoke(BookChangeEvent $event): void
    {
        $oldBook = $event->oldBook;
        $newBook = $event->newBook;

        $oldBookAsArray = is_null($oldBook) ? [] : $oldBook->getAsArray();
        $newBookAsArray = is_null($newBook) ? [] : $newBook->getAsArray();

        $message = "{oldBook} {newBook}\r\n";

        $context = [
            'oldBook' => $this->valueToStringHelper->valueToString($oldBookAsArray),
            'newBook' => $this->valueToStringHelper->valueToString($newBookAsArray),
        ];

        $this->logger->info($message, $context);
    }
}
