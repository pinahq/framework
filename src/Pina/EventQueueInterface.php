<?php

namespace Pina;

interface EventQueueInterface
{

    public function push(string $handler, string $data, int $priority);

}
