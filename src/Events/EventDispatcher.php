<?php

declare(strict_types=1);

namespace Rukavishnikov\Php\Basic\App\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var callable[][]
     */
    private array $listeners = [];

    /**
     * @param callable[][] $listeners
     */
    public function __construct(array $listeners)
    {
        foreach ($listeners as $eventName => $listener) {
            $this->addListener($eventName, $listener);
        }
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event)
    {
        $eventName = get_class($event);

        foreach ($this->getListenersForEvent($eventName) as $listener) {
            $listener($event);
        }
    }

    /**
     * @param string $eventName
     * @param callable $listener
     * @return void
     */
    private function addListener(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * @param string $eventName
     * @return callable[]
     */
    private function getListenersForEvent(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
