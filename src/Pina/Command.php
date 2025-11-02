<?php


namespace Pina;


use Pina\Events\Priority;
use Pina\Queue\QueueableCommand;

abstract class Command
{

    protected $input = '';
    protected $before = [];
    protected $after = [];

    abstract protected function execute($input = '');

    public static function before(Command $command): Command
    {
        $instance = static::load();
        $instance->before[] = $command;
        return $instance;
    }

    public static function then(Command $command): Command
    {
        $instance = static::load();
        $instance->after[] = $command;
        return $instance;
    }

    public static function load(): Command
    {
        return App::load(static::class);
    }

    public static function queueable($priority = Priority::NORMAL): QueueableCommand
    {
        return new QueueableCommand(static::class, $priority);
    }

    public static function run($input = '')
    {
        $cmd = static::load();
        return $cmd($input);
    }

    public static function enqueue($input = '', $priority = Priority::NORMAL)
    {
        $cmd = static::queueable($priority);
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