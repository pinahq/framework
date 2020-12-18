<?php

namespace Pina\Events;

use Pina\App;

class EventManager implements EventManagerInterface
{

    protected $registry = [];
    protected $handlers = [];

    /**
     * 
     * @param string $event
     * @param int $mode
     * @param int $priority
     * @return EventRegistry
     */
    protected function getRegistry($event, $mode, $priority)
    {
        if (!isset($this->registry[$mode])) {
            $this->registry[$mode] = [];
        }

        if (!isset($this->registry[$mode][$event])) {
            $this->registry[$mode][$event] = [];
        }


        if (!isset($this->registry[$mode][$event][$priority])) {
            $this->registry[$mode][$event][$priority] = new EventRegistry();
        }

        return $this->registry[$mode][$event][$priority];
    }

    public function subscribe($eventKey, $handler, $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        $isAsync = App::container()->has(EventQueueInterface::class) ? 1 : 0;
        $this->getRegistry($eventKey, $isAsync, $priority)->push($handler);
        $this->handlers[$handler->getKey()] = $handler;
    }

    public function subscribeSync($eventKey, $handler, $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        $this->getRegistry($eventKey, 0, $priority)->push($handler);
        $this->handlers[$handler->getKey()] = $handler;
    }

    public function handle($key, $payload)
    {
        $this->getHandler($key)->handle($payload);
    }

    public function getHandler($key)
    {
        if (!isset($this->handlers[$key])) {
            throw new Exception(__("Не найден обработчик для события:") . ' ' . $key);
        }

        return $this->handlers[$key];
    }

    public function getHandlerKeys()
    {
        return array_keys($this->handlers);
    }

    public function trigger($eventKey, $payload = '')
    {
        $this->triggerWithPriority($eventKey, $payload, 0);
        $this->triggerWithPriority($eventKey, $payload, 1);
        $this->triggerWithPriority($eventKey, $payload, 2);
    }

    protected function triggerWithPriority($eventKey, $payload, $priority)
    {
        //sync
        $registry = $this->getRegistry($eventKey, 0, $priority);
        foreach ($registry as $handler) {
            $handler->handle($payload);
        }

        //async
        $registry = $this->getRegistry($eventKey, 1, $priority);
        $queue = App::container()->get(\Pina\EventQueueInterface::class);
        foreach ($registry as $handler) {
            $queue->push($handler, $payload, $priority);
        }
    }

}
