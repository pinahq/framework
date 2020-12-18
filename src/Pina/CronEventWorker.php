<?php

namespace Pina;

class CronEventWorker
{

    public function work()
    {

        while ($event = CronEventGateway::instance()->orderBy('priority', 'asc')->orderBy('created', 'asc')->first()) {
            list($namespace, $script) = explode('::', $event['event']);
            
            \Pina\App::events()->getHandler($event['event'])->handle($event['data']);

            CronEventGateway::instance()->whereId($event['id'])->delete();
        }
    }

}
