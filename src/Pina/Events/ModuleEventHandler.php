<?php

namespace Pina\Events;

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
        return \implode(':::', ['module', $this->namespace, $this->script]);
    }
    
    public function handle($payload)
    {
        $handler = new \Pina\EventHandler($this->namespace, $this->script, $payload);
        Event::push($handler);
        Event::run();
        Event::pop();
    }
}