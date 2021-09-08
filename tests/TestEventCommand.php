<?php


use Pina\Command;

class TestEventCommand extends Command
{

    protected $handler;

    public function __construct()
    {
        $this->handler = new TestEventHandler('low');
    }

    protected function execute($input = '')
    {
        return $this->handler->handle($input);
    }

}