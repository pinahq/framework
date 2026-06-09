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

    public static function queueableAsUnique($priority = Priority::NORMAL): QueueableCommand
    {
        return new QueueableCommand(static::class, $priority, true);
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

    public static function enqueueAsUnique($input = '', $priority = Priority::NORMAL)
    {
        $cmd = static::queueableAsUnique($priority);
        return $cmd($input);
    }

    public function __invoke($input = '')
    {
        $this->input = $input;

        $this->log('Started');

        foreach ($this->before as $cmd) {
            $cmd($input);
        }

        $output = $this->execute($input);

        foreach ($this->after as $cmd) {
            $cmd($input);
        }

        $this->log('Done', $output);

        return $output;
    }

    public function __toString()
    {
        return get_class($this) . '(' . $this->input . ')';
    }

    protected function log($message, $output = null)
    {
        $log = $this->__toString() . ' ' . $message;
        $context = [];
        if (!empty($output)) {
            if (is_object($output)) {
                $context['output'] = $output;
            }
            if (is_string($output)) {
                $log .= ': ' . $output;
            }
        }
        Log::info('command', $log, $context);
    }

}