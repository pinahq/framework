<?php

namespace Pina\Events;

use Pina\App;
use Pina\EventQueueInterface;

class QueuedListener
{

    protected $listener = '';
    protected $priority = 0;

    public function __construct(string $listener, int $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function __invoke(Event $event)
    {
        if (!$event->queueable() || !App::container()->has(EventQueueInterface::class)) {
            $handler = App::load($this->listener);
            return $handler($event);
        }

        $this->queue(json_encode([$this->listener, get_class($event), $event->serialize()], JSON_UNESCAPED_UNICODE));

        return '';
    }

    protected function queue(string $data)
    {
        $this->makeQueue()->push(QueueHandler::class, $data, $this->priority);
    }

    protected function makeQueue(): EventQueueInterface
    {
        return App::container()->get(EventQueueInterface::class);
    }
}