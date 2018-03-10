<?php

namespace Pina;

class CronEventQueue implements EventQueueInterface
{

    public function push($handler, $data)
    {
        CronEventGateway::instance()->insert([
            'event' => $handler,
            'data' => $data,
        ]);
    }

}
