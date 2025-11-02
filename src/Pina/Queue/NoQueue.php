<?php

namespace Pina\Queue;

use Pina\App;
use Pina\Command;

class NoQueue implements EventQueueInterface
{
    public function push(string $handler, string $data, int $priority, bool $unique = false)
    {
        /** @var Command $cmd */
        $cmd = App::load($handler);
        $cmd($data);
    }
}