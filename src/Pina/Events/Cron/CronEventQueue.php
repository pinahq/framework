<?php

namespace Pina\Events\Cron;

use Pina\EventQueueInterface;

class CronEventQueue implements EventQueueInterface
{

    public function push($handler, $data, $priority, $delay)
    {
        CronEventGateway::instance()->insert([
            //временное решение, в будущем $handler будет только строкой с именем класса типа Command, а EventHandlerInterface уберем
            'event' => is_string($handler) ? $handler : $handler->getKey(),
            'data' => $data,
            'priority' => $priority,
            'delay' => $delay,
        ]);
    }

}
