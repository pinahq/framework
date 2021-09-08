<?php


namespace Pina\Events;


use Exception;
use Pina\App;
use Pina\Command;
use Pina\EventQueueInterface;

class QueueableCommand extends Command
{
    protected $cmd;
    protected $priority;
    protected $delay = 0;

    /**
     * QueueableCommand constructor.
     * @param string $cmd
     * @param int $priority
     * @throws Exception
     */
    public function __construct(string $cmd, $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        if (!class_exists($cmd)) {
            throw new Exception('Expected an existsed class ' . $cmd);
        }
        if (!in_array(Command::class, class_parents($cmd))) {
            throw new Exception('Expected a ' . Command::class . ' class child');
        }
        $this->cmd = $cmd;
        $this->priority = $priority;
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;
    }

    protected function execute($data = '')
    {
        if (!App::container()->has(EventQueueInterface::class)) {
            /** @var Command $cmd */
            $cmd = App::load($this->cmd);
            return $cmd();
        }

        /** @var EventQueueInterface $queue */
        $queue = App::container()->get(EventQueueInterface::class);
        $queue->push($this->cmd, $data, $this->priority, $this->delay);

        return '';
    }


}