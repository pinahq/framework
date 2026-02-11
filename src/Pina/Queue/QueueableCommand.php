<?php


namespace Pina\Queue;


use Exception;
use Pina\App;
use Pina\Command;
use Pina\Events\Priority;

class QueueableCommand extends Command
{
    protected $cmd;
    protected $priority;
    protected bool $unique;

    /**
     * QueueableCommand constructor.
     * @param string $cmd
     * @param int $priority
     * @throws Exception
     */
    public function __construct(string $cmd, int $priority = Priority::NORMAL, bool $unique = false)
    {
        if (!class_exists($cmd)) {
            throw new Exception('Expected an existsed class ' . $cmd);
        }
        if (!in_array(Command::class, class_parents($cmd))) {
            throw new Exception('Expected a ' . Command::class . ' class child');
        }
        $this->cmd = $cmd;
        $this->priority = $priority;
        $this->unique = $unique;
    }

    protected function execute($data = '')
    {
        App::queue()->push($this->cmd, $data, $this->priority, $this->unique);
        return '';
    }

}