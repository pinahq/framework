<?php

use Pina\EventQueueInterface;

class TestEventQueue implements EventQueueInterface
{
    
    protected $data = [];

    public function push($handler, $data, $priority)
    {
        $this->data[] = [$handler, $data, $priority];
        usort($this->data, [$this, 'sort']);
    }
    
    public function sort($a, $b)
    {
        return $a[2] - $b[2];
    }
    
    public function work()
    {
        foreach ($this->data as $line) {
            list($handler, $data, $priority) = $line;
            $handler->handle($data);
        }
    }

}
