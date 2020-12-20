<?php

namespace Pina\Events;

use Pina\Event;

class ModuleEventHandler implements EventHandlerInterface
{
    protected $namespace = '';
    protected $script = '';
    
    public function __construct($namespace, $script)
    {
        $this->namespace = $namespace;
        $this->script = $script;
    }
    
    public function getKey()
    {
        return \implode('::', [$this->namespace, $this->script]);
    }
    
    public function handle($payload)
    {
        $handler = new \Pina\EventHandler($this->namespace, $this->script, $payload);
        Event::push($handler);
        Event::run();
        Event::pop();
    }
}