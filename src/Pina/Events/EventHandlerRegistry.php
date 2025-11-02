<?php


namespace Pina\Events;

use Exception;
use \Pina\Command;

class EventHandlerRegistry
{
    /** @var Command[][] */
    protected $handlers = [];

    public function __construct()
    {
        $this->handlers[Priority::HIGH] = [];
        $this->handlers[Priority::NORMAL] = [];
        $this->handlers[Priority::LOW] = [];
    }

    public function subscribe(Command $handler, $priority = Priority::NORMAL)
    {
        if (!isset($this->handlers[$priority])) {
            throw new Exception();
        }
        $this->handlers[$priority][] = $handler;
        return $this;
    }

    public function subscribeWithLowPriority(Command $handler)
    {
        return $this->subscribe($handler, Priority::LOW);
    }

    public function subscribeWithHighPriority(Command $handler)
    {
        return $this->subscribe($handler, Priority::HIGH);
    }

    public function trigger($data)
    {
        foreach ($this->handlers as $priority => $hs) {
            foreach ($hs as $handler) {
                $handler($data);
            }
        }
        return $this;
    }

}