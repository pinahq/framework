<?php

namespace Pina\Events\Cron;

use Exception;
use Pina\App;
use Pina\Command;
use Pina\Log;

class CronEventWorker
{

    public function work($workerId, $taskLimit, $restSeconds, $pushOffSeconds, $priority)
    {
        Log::info('event', 'start worker ' . $workerId);
        $i = 0;
        while (1) {
            //Если при старте воркера обнаружились незавершенные задачи,
            //скорее всего старый воркер выпал по какой-то ошибке
            //и мы отложим их на более поздний срок, чтобы не блокировать новые задачи
            while ($task = $this->getNextTask($workerId)) {
                $this->pushOff($task['id'], $pushOffSeconds);
            }

            while ($this->assignTask($workerId, $priority)) {
                while ($task = $this->getNextTask($workerId)) {
                    Log::info('event', 'Worker ' . $workerId . ' has started ' . $task['event']);
                    $this->startTask($task['id']);
                    try {
                        if (class_exists($task['event']) && in_array(Command::class, class_parents($task['event']))) {
                            $cmd = App::load($task['event']);
                            $cmd($task['data']);
                        } else {
                            //@deprecated
                            App::events()->getHandler($task['event'])->handle($task['data']);
                        }
                        $this->deleteTask($task['id']);
                    } catch (Exception $e) {
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

    protected function assignTask($workerId, $priority)
    {
        return CronEventGateway::instance()
            ->whereNull('worker_id')
            ->whereScheduled()
            //выбираем задачи с приоритетом не меньше воркера
            ->where('priority <= '.intval($priority))
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
