<?php

namespace Pina\Events;

use Pina\Command;

class QueueHandler extends Command
{
    protected function execute($input = '')
    {
        $data = json_decode($input, true);
        if (!is_array($data) || count($data) != 3) {
            return '';
        }

        list($listenerClass, $eventClass, $params) = $data;

        if (!in_array(Event::class, class_parents($eventClass))) {
            return '';
        }

        $event = new $eventClass(...$params);

        /** @var callable $listener */
        $listener = new $listenerClass();
        return $listener($event);
    }
}