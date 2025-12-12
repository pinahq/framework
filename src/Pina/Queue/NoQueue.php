<?php

namespace Pina\Queue;

use Pina\App;
use Pina\Command;

class NoQueue extends Queue
{
    public function push(string $handler, string $payload, int $priority, bool $unique = false)
    {
        /** @var Command $cmd */
        $cmd = App::load($handler);
        $cmd($payload);
    }
}