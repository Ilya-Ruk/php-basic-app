<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Events;

use Psr\Log\LoggerInterface;
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
        $oldBookAsArray = $event->oldBook->getAsArray();
        $newBookAsArray = $event->newBook->getAsArray();

        $message = "{oldData} {newData}\r\n";

        $context = [
            'oldData' => $this->valueToStringHelper->valueToString($oldBookAsArray),
            'newData' => $this->valueToStringHelper->valueToString($newBookAsArray),
        ];

        $this->logger->info($message, $context);
    }
}
