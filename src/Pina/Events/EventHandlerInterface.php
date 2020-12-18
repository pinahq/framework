<?php

namespace Pina\Events;

interface EventHandlerInterface
{

    public function getKey();

    public function handle($payload);
}
