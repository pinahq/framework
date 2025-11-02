<?php

namespace Pina\Events;

use Pina\App;

abstract class Event
{

    abstract public function queueable(): bool;

    abstract public function serialize(): array;

    public static function subscribe(callable $handler, int $priority = Priority::NORMAL)
    {
        /** @var Bus $bus */
        $bus = App::load(Bus::class);
        $bus->subscribe(static::class, $handler, $priority);
    }

    public static function subscribeWithLowPriority(callable $handler)
    {
        return static::subscribe($handler, Priority::LOW);
    }

    public static function subscribeWithHighPriority(callable $handler)
    {
        return static::subscribe($handler, Priority::HIGH);
    }

    public function trigger()
    {
        /** @var Bus $bus */
        $bus = App::load(Bus::class);
        $bus->trigger($this);
    }

}