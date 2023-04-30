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
                        if (class_exists($task['event']) && in_array(Command::class, class_parents($task['event']))) {
                            $cmd = App::load($task['event']);
                            $cmd($task['data']);
                        } else {
                            Log::error('event', 'Handler not found', $task);
                        }
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

    protected function assignTask(int $workerId, int $priority)
    {
        /** @var CronEventGateway $query */
        $query = CronEventGateway::instance()
            ->leftJoin(
                CronEventWorkerGateway::instance()->on('id', 'id')
                    ->whereNull('worker_id')
            )
            ->whereScheduled()
            //выбираем задачи с приоритетом не меньше воркера
            ->where('priority <= '.intval($priority))
            ->limit(1)
            ->orderBy('priority', 'asc')
            ->orderBy('scheduled_at', 'asc');

        return CronEventWorkerGateway::instance()->insertFromEvents($workerId, $query);
    }

    protected function getNextTask($workerId)
    {
        return CronEventGateway::instance()
            ->selectAll()
            ->innerJoin(
                CronEventWorkerGateway::instance()->on('id', 'id')
                    ->onBy('worker_id', $workerId)
            )
            ->first();
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
        CronEventWorkerGateway::instance()->whereId($id)->delete();
    }

    /**
     * @param string $id
     * @param int $delay
     * @throws Exception
     */
    protected function pushOff(string $id, int $delay)
    {
        CronEventGateway::instance()->whereId($id)->pushOff($delay);
        CronEventWorkerGateway::instance()->whereId($id)->delete();
    }

}
