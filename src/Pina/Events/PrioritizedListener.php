<?php

namespace Pina\Events;

class PrioritizedListener
{
    /** @var callable */
    protected $listener;

    protected $priority = 0;

    public function __construct(callable $listener, int $priority = 0)
    {
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function __invoke(Event $event)
    {
        $listener = $this->listener;
        return $listener($event);
    }

}