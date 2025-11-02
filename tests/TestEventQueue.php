<?php

use Pina\App;
use Pina\Queue\EventQueueInterface;

class TestEventQueue implements EventQueueInterface
{
    
    protected $data = [];

    public function push(string $handler, string $data, int $priority, bool $unique = false)
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
        while ($line = array_shift($this->data)) {
            list($handler, $data, $priority) = $line;
            if (is_string($handler)) {
                $cmd = App::load($handler);
                $cmd($data);
            } else {
                $handler->handle($data);
            }
        }
    }

}
