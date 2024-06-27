<?php

namespace Pina\Events;

use Pina\App;

abstract class Event
{

    abstract public function queueable(): bool;

    abstract public function serialize(): array;

    public static function subscribe(callable $handler, int $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        /** @var Bus $bus */
        $bus = App::load(Bus::class);
        $bus->subscribe(static::class, $handler, $priority);
    }

    public function trigger()
    {
        /** @var Bus $bus */
        $bus = App::load(Bus::class);
        $bus->trigger($this);
    }

}