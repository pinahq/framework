<?php

namespace Pina\Events;

use Pina\App;
use Pina\Event;
use function Pina\__;

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

    public function subscribe($eventKey, $handler, $priority = Event::PRIORITY_NORMAL)
    {
        $mode = App::container()->has(\Pina\EventQueueInterface::class) ? Event::MODE_ASYNC : Event::MODE_SYNC;

        $this->getRegistry($eventKey, $mode, $priority)->push($handler);
        $this->handlers[$handler->getKey()] = $handler;
    }

    public function subscribeSync($eventKey, $handler, $priority = Event::PRIORITY_NORMAL)
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
            throw new \Exception(__("Не найден обработчик для события:") . ' ' . $key);
        }

        return $this->handlers[$key];
    }

    public function getHandlerKeys()
    {
        return array_keys($this->handlers);
    }

    public function trigger($eventKey, $payload = '')
    {
        $this->triggerWithPriority($eventKey, $payload, Event::PRIORITY_HIGH);
        $this->triggerWithPriority($eventKey, $payload, Event::PRIORITY_NORMAL);
        $this->triggerWithPriority($eventKey, $payload, Event::PRIORITY_LOW);
    }

    protected function triggerWithPriority($eventKey, $payload, $priority)
    {
        //sync
        $registry = $this->getRegistry($eventKey, \Pina\Event::MODE_SYNC, $priority);
        foreach ($registry as $handler) {
            $handler->handle($payload);
        }

        if (!App::container()->has(\Pina\EventQueueInterface::class)) {
            return;
        }
        //async
        $registry = $this->getRegistry($eventKey, \Pina\Event::MODE_ASYNC, $priority);
        $queue = App::container()->get(\Pina\EventQueueInterface::class);
        foreach ($registry as $handler) {
            $queue->push($handler, $payload, $priority);
        }
    }

}
