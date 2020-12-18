<?php

namespace Pina\Events;

interface EventManagerInterface
{
    /**
     * 
     * @param string $eventKey
     * @param \Pina\Events\EventHandlerInterface $handler
     * @param int $priority
     */
    public function subscribe($eventKey, $handler, $priority = \Pina\Event::PRIORITY_NORMAL);

    /**
     * 
     * @param string $eventKey
     * @param \Pina\Events\EventHandlerInterface $handler
     * @param int $priority
     */
    public function subscribeSync($eventKey, $handler, $priority = \Pina\Event::PRIORITY_NORMAL);

    /**
     * 
     * @param string $key
     * @return EventHandlerInterface
     */
    public function getHandler($key);
    
    /**
     * 
     * @return string[]
     */
    public function getHandlerKeys();

    /**
     * 
     * @param string $eventKey
     * @param string $payload
     */
    public function trigger($eventKey, $payload = '');

}
