<?php

namespace Pina\Queue;

use Pina\Container\SingletonTrait;

class Queue
{
    use SingletonTrait;

    public function push(string $handler, string $payload, int $priority, bool $unique = false)
    {
        if ($unique) {
            $queued = QueueGateway::instance()
                ->whereBy('handler', $handler)
                ->whereBy('payload', $payload)
                ->exists();

            if ($queued) {
                return;
            }
        }

        $e = [
            'handler' => $handler,
            'payload' => $payload,
            'priority' => $priority,
            'delay' => 0,//первое событие обрабатывается без задержек
        ];
        QueueGateway::instance()->insertIgnore($e);
    }

}
