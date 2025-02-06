<?php

namespace Pina\Events\Cron;

use Pina\EventQueueInterface;

class CronEventQueue implements EventQueueInterface
{

    public function push(string $handler, string $data, int $priority, bool $unique = false)
    {
        if ($unique) {
            $queued = CronEventGateway::instance()
                ->whereBy('event', $handler)
                ->whereBy('data', $data)
                ->whereNull('worker_id')
                ->exists();

            if ($queued) {
                return;
            }
        }

        $event = [
            'event' => $handler,
            'data' => $data,
            'priority' => $priority,
            'delay' => 0,//первое событие обрабатывается без задержек
        ];
        CronEventGateway::instance()->insertIgnore($event);
    }

}
