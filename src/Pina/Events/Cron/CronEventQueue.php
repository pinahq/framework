<?php

namespace Pina\Events\Cron;

use Pina\EventQueueInterface;

class CronEventQueue implements EventQueueInterface
{

    public function push(string $handler, string $data, int $priority)
    {
        CronEventGateway::instance()->insertIgnore([
                                                       'event' => $handler,
                                                       'data' => $data,
                                                       'priority' => $priority,
                                                       'delay' => 0,//первое событие обрабатывается без задержек
                                                   ]);
    }

}
