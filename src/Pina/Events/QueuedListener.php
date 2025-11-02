<?php

namespace Pina\Events;

use Pina\App;

class QueuedListener
{

    protected $listener = '';
    protected $priority = 0;

    public function __construct(string $listener, int $priority = Priority::NORMAL)
    {
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function __invoke(Event $event)
    {
        if (!$event->queueable()) {
            $handler = App::load($this->listener);
            return $handler($event);
        }

        $this->queue(json_encode([$this->listener, get_class($event), $event->serialize()], JSON_UNESCAPED_UNICODE));

        return '';
    }

    protected function queue(string $data)
    {
        App::queue()->push(QueueHandler::class, $data, $this->priority);
    }
}