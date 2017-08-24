<?php

namespace Pina;

interface EventQueueInterface
{

    public function push($handler, $data);

}
