<?php

namespace Pina\Events\Cron;

use Pina\EventQueueInterface;

class CronEventQueue implements EventQueueInterface
{

    public function push($handler, $data, $priority, $delay)
    {
        CronEventGateway::instance()->insert([
            'event' => $handler->getKey(),
            'data' => $data,
            'priority' => $priority,
            'delay' => $delay,
        ]);
    }

}
