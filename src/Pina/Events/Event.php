<?php

namespace Pina\Events;

abstract class Event
{

    abstract public function queueable(): bool;

    abstract public function serialize(): array;

    public static function subscribe(callable $handler, int $priority = Priority::NORMAL)
    {
        Bus::load()->subscribe(static::class, $handler, $priority);
    }

    public static function subscribeWithLowPriority(callable $handler)
    {
        static::subscribe($handler, Priority::LOW);
    }

    public static function subscribeWithHighPriority(callable $handler)
    {
        static::subscribe($handler, Priority::HIGH);
    }

    public function trigger()
    {
        Bus::load()->trigger($this);
    }

}