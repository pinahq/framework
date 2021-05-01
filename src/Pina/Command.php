<?php


namespace Pina;


abstract class Command
{

    protected $input = '';
    protected $before = [];
    protected $after = [];

    abstract function execute($input = '');

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
        $this->input = $input;

        foreach ($this->before as $cmd) {
            $cmd($input);
        }

        $output = $this->execute($input);

        foreach ($this->after as $cmd) {
            $cmd($output);
        }

        return $output;
    }

    public function __toString()
    {
        return get_class($this) . '(' . $this->input . ')';
    }

}