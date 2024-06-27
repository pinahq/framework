<?php

namespace Pina\Events;

class Bus
{

    protected $listeners = [];

    public function subscribe(string $eventName, callable $handler, int $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = new PrioritizedListener($handler, $priority);

        usort($this->listeners[$eventName], function (PrioritizedListener $a, PrioritizedListener $b) {
            return $a->getPriority() > $b->getPriority();
        });
    }

    public function trigger(Event $event)
    {
        $eventName = get_class($event);
        if (!isset($this->listeners[$eventName]) || !is_array($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            $listener($event);
        }
    }

}