<?php

namespace Pina\Events;

class UniqueQueuedListener extends QueuedListener
{

    protected function queue(string $data)
    {
        $this->makeQueue()->push(QueueHandler::class, $data, $this->priority, true);
    }

}