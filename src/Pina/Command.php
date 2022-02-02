<?php


namespace Pina;


abstract class Command
{

    protected $input = '';
    protected $before = [];
    protected $after = [];

    abstract protected function execute($input = '');

    public function before($command)
    {
        $this->before[] = $command;
        return $this;
    }

    public function then($command)
    {
        $this->after[] = $command;
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