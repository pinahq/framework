<?php

use PHPUnit\Framework\TestCase;
use Pina\Command;
use Pina\Scheduler;

class CommandTest extends TestCase
{

    public function testCommand()
    {
        $accumulator = AccumulatorCommand::load();
        $command = EchoCommand::load();
        $command->then($accumulator);

        $this->assertEquals('1', $command('1'));

        $this->assertEquals(1, $accumulator->get());
        $command('1');
        $this->assertEquals(2, $accumulator->get());
        $command('1');
        $command('1');
        $this->assertEquals(4, $accumulator->get());
        $command->before($accumulator);
        $command('1');
        $this->assertEquals(6, $accumulator->get());
    }


    public function testScheduler()
    {
        $scheduler = new Scheduler();
        $counter = new CounterCommand();
        $scheduler->everyMinute($counter);
        $scheduler->run();

        $this->assertEquals(1, $counter->get());
    }
}

class EchoCommand extends Command
{

    public function execute($input = '')
    {
        return $input;
    }

}

class AccumulatorCommand extends Command
{
    protected $data = 0;

    public function execute($input = '')
    {
        $this->data += intval($input);
    }

    public function get()
    {
        return $this->data;
    }
}

class CounterCommand extends Command
{
    protected $data = 0;

    public function execute($input = '')
    {
        $this->data++;
    }

    public function get()
    {
        return $this->data;
    }
}