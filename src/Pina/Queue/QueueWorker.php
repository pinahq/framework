<?php

namespace Pina\Queue;

use Exception;
use Pina\App;
use Pina\Command;
use Pina\Log;

class QueueWorker
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
        Log::info('queue', 'start worker ' . $workerId);
        $i = 0;
        while (1) {
            //Если при старте воркера обнаружились незавершенные задачи,
            //скорее всего старый воркер выпал по какой-то ошибке
            //и мы отложим их на более поздний срок, чтобы не блокировать новые задачи
            while ($task = $this->getNextTask($workerId)) {
                $this->pushOff($task['id'], $pushOffSeconds, 'reload');
            }

            while ($this->assignTask($workerId, $priority)) {
                while ($task = $this->getNextTask($workerId)) {
                    Log::info('queue', 'Worker ' . $workerId . ' has started ' . $task['handler']);
                    $this->startTask($task['id']);
                    try {
                        $this->runTask($task);
                        $this->deleteTask($task['id']);
                    } catch (Exception $e) {
                        Log::error('queue', $task['id'] . ' ' . $task['handler'] . ': ' .$e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), $e->getTrace());

                        //откладываем задачу на $pushOffSeconds
                        $this->pushOff($task['id'], $pushOffSeconds, (get_class($e)) .': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
        if (class_exists($task['handler']) && in_array(Command::class, class_parents($task['handler']))) {
            $cmd = App::load($task['handler']);
            $cmd($task['payload']);
        } else {
            Log::error('queue', 'Handler not found', $task);
        }
    }

    protected function assignTask(int $workerId, int $priority)
    {
        /** @var QueueGateway $query */
        $taskId = QueueGateway::instance()
            ->whereNull('worker_id')
            ->whereScheduled()
            //выбираем задачи с приоритетом не меньше воркера
            ->where('priority <= '.intval($priority))
            ->orderBy('priority', 'asc')
            ->orderBy('scheduled_at', 'asc')
            ->id();

        return QueueGateway::instance()
            ->whereId($taskId)
            ->whereNull('worker_id')
            ->update(['worker_id' => $workerId]);
    }

    protected function getNextTask($workerId)
    {
        return QueueGateway::instance()->whereBy('worker_id', $workerId)->first();
    }

    /**
     * @param string $id
     * @throws Exception
     */
    protected function startTask(string $id)
    {
        QueueGateway::instance()->whereId($id)->start();
    }

    /**
     * @param string $id
     * @throws Exception
     */
    protected function deleteTask(string  $id)
    {
        QueueGateway::instance()->whereId($id)->delete();
    }

    /**
     * @param string $id
     * @param int $delay
     * @throws Exception
     */
    protected function pushOff(string $id, int $delay, string $message)
    {
        QueueGateway::instance()->whereId($id)->pushOff($delay, $message);
    }

}
