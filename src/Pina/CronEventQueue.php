<?php

namespace Pina;

class CronEventQueue implements EventQueueInterface
{

    public function push($handler, $data, $priority)
    {
        CronEventGateway::instance()->insert([
            'event' => $handler->getKey(),
            'data' => $data,
            'priority' => $priority,
        ]);
    }

}
