<?php

namespace Pina\Events\Cron;

use Exception;
use Pina\App;
use Pina\Command;
use Pina\Log;

class CronEventWorker
{
    /**
     * @param int $workerId
     * @param int $taskLimit
     * @param int $restSeconds
     * @param int $pushOffSeconds
     * @param int $priority
     * @throws Exception
     */
    public function work(int $workerId, int $taskLimit, int $restSeconds, int $pushOffSeconds, int $priority)
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
                        $this->runTask($task);
                        $this->deleteTask($task['id']);
                    } catch (Exception $e) {
                        Log::error('event', $e->getMessage(), $task);
                        //откладываем задачу на $pushOffSeconds
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

    protected function runTask($task)
    {
        if (class_exists($task['event']) && in_array(Command::class, class_parents($task['event']))) {
            $cmd = App::load($task['event']);
            $cmd($task['data']);
        } else {
            Log::error('event', 'Handler not found', $task);
        }
    }

    protected function assignTask(int $workerId, int $priority)
    {
        /** @var CronEventGateway $query */
        $taskId = CronEventGateway::instance()
            ->whereNull('worker_id')
            ->whereScheduled()
            //выбираем задачи с приоритетом не меньше воркера
            ->where('priority <= '.intval($priority))
            ->orderBy('priority', 'asc')
            ->orderBy('scheduled_at', 'asc')
            ->id();

        return CronEventGateway::instance()
            ->whereId($taskId)
            ->whereNull('worker_id')
            ->update(['worker_id' => $workerId]);
    }

    protected function getNextTask($workerId)
    {
        return CronEventGateway::instance()->whereBy('worker_id', $workerId)->first();
    }

    /**
     * @param string $id
     * @throws Exception
     */
    protected function startTask(string $id)
    {
        CronEventGateway::instance()->whereId($id)->start();
    }

    /**
     * @param string $id
     * @throws Exception
     */
    protected function deleteTask(string  $id)
    {
        CronEventGateway::instance()->whereId($id)->delete();
    }

    /**
     * @param string $id
     * @param int $delay
     * @throws Exception
     */
    protected function pushOff(string $id, int $delay)
    {
        CronEventGateway::instance()->whereId($id)->pushOff($delay);
    }

}
