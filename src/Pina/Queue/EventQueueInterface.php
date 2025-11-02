<?php

namespace Pina\Queue;

interface EventQueueInterface
{

    public function push(string $handler, string $data, int $priority, bool $unique = false);

}
