<?php

namespace Pina\Events;

use Pina\App;

class UniqueQueuedListener extends QueuedListener
{

    protected function queue(string $data)
    {
        App::queue()->push(QueueHandler::class, $data, $this->priority, true);
    }

}