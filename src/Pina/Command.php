<?php


namespace Pina;


use Pina\Events\QueueableCommand;

abstract class Command
{

    protected $input = '';
    protected $before = [];
    protected $after = [];

    abstract protected function execute($input = '');

    public function before(Command $command)
    {
        $this->before[] = $command;
        return $this;
    }

    public function then(Command $command)
    {
        $this->after[] = $command;
    }

    public static function load(): Command
    {
        return App::load(static::class);
    }

    public static function run($input = '')
    {
        $cmd = static::load();
        return $cmd($input);
    }

    public static function queue($input = '', $priority = \Pina\Event::PRIORITY_NORMAL)
    {
        $cmd = new QueueableCommand(static::class, $priority);
        return $cmd($input);
    }

    public function __invoke($input = '')
    {
        Log::info('command', 'Started', ['class' => get_class($this), 'input' => $input]);

        $this->input = $input;

        foreach ($this->before as $cmd) {
            $cmd($input);
        }

        $output = $this->execute($input);

        foreach ($this->after as $cmd) {
            $cmd($input);
        }

        Log::info('command', 'Done', ['class' => get_class($this), 'input' => $input, 'output' => $output]);
        return $output;
    }

    public function __toString()
    {
        return get_class($this) . '(' . $this->input . ')';
    }

}