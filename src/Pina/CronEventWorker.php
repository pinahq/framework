<?php

namespace Pina;

class CronEventWorker
{

    public function work()
    {

        while ($event = CronEventGateway::instance()->orderBy('created', 'asc')->first()) {
            list($namespace, $script) = explode('::', $event['event']);

            $handler = new EventHandler($namespace, $script, $event['data']);
            Event::push($handler);
            Event::run();
            Event::pop();
            
            CronEventGateway::instance()->whereId($event['id'])->delete();
        }
    }

}
