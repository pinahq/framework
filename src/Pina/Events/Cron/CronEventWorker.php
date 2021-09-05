<?php

namespace Pina\Events\Cron;

use Pina\Log;

class CronEventWorker
{

    public function work($workerId, $taskLimit, $restSeconds, $pushOffSeconds)
    {
        Log::info('event', 'start worker ' . $workerId);
        $i = 0;
        while (1) {
            while ($this->assignTask($workerId)) {
                //пытаемся завершить ранее начатые задачи
                while ($task = $this->getNextTask($workerId)) {
                    Log::info('event', 'Worker ' . $workerId . ' has started ' . $task['event']);
                    $this->startTask($task['id']);
                    try {
                        \Pina\App::events()->getHandler($task['event'])->handle($task['data']);
                        $this->deleteTask($task['id']);
                    } catch (\Exception $e) {
                        Log::error('event', $e->getMessage(), $task);
                        //откладываем задачу на час
                        $this->pushOff($task['id'], $pushOffSeconds);
                    }
                }
                if ($i++ > $taskLimit) {
                    break 2;
                }
            }
            sleep($restSeconds);
        }
    }

    protected function assignTask($workerId)
    {
        return CronEventGateway::instance()
            ->whereNull('worker_id')
            ->whereScheduled()
            ->limit(1)
            ->orderBy('priority', 'asc')
            ->orderBy('scheduled_at', 'asc')
            ->update(['worker_id' => $workerId]);
    }

    protected function getNextTask($workerId)
    {
        return CronEventGateway::instance()->whereBy('worker_id', $workerId)->first();
    }

    protected function startTask($id)
    {
        CronEventGateway::instance()->whereId($id)->start();
    }

    protected function deleteTask($id)
    {
        CronEventGateway::instance()->whereId($id)->delete();
    }

    protected function pushOff($id, $delay)
    {
        CronEventGateway::instance()->whereId($id)->pushOff($delay);
    }

}
