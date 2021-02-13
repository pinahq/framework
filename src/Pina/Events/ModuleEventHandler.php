<?php

namespace Pina\Events;

use Pina\Event;
use Pina\ModuleInterface;

class ModuleEventHandler implements EventHandlerInterface
{
    /** @var ModuleInterface */
    protected $module = '';
    
    /** @var string */
    protected $script = '';
    
    /**
     * 
     * @param ModuleInterface $module
     * @param string $script
     */
    public function __construct($module, $script)
    {
        $this->module = $module;
        $this->script = $script;
    }
    
    public function getKey()
    {
        return \implode('::', [$this->module->getNamespace(), $this->script]);
    }
    
    public function handle($payload)
    {
        $handler = new \Pina\EventHandler($this->module, $this->script, $payload);
        Event::push($handler);
        Event::run();
        Event::pop();
    }
}