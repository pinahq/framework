<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\Events\Priority;

require __DIR__ . '/TestEventHandler.php';
require __DIR__ . '/TestEventQueue.php';
require __DIR__ . '/TestEventCommand.php';
require __DIR__ . '/TestEvent.php';

class EventTest extends TestCase
{

    public function testNewEvent()
    {
        global $testEventBuffer;

        $testEventBuffer = '';

        App::container()->share(\Pina\Queue\EventQueueInterface::class, new \TestEventQueue());
        $queue = App::queue();

        App::event('order.placed')->subscribe(\TestEventCommand::queueable());
        App::event('order.placed')->trigger('2');

        $queue->work();
        $this->assertEquals('low2', $testEventBuffer);

        App::event('order.placed')->trigger('3');
        App::event('order.placed')->trigger('4');

        $queue->work();
        $this->assertEquals('low2low3low4', $testEventBuffer);
    }

    public function testModelEvents()
    {
        $returnedText = '';
        TestEvent::subscribe(function(TestEvent $event) use (&$returnedText) {
            $returnedText .= '-' . $event->getText();
        }, Priority::LOW);

        TestEvent::subscribe(function(TestEvent $event) use (&$returnedText) {
            $returnedText .= '+' . $event->getText();
        }, Priority::HIGH);

        $event = new TestEvent('A');
        $event->trigger();

        $this->assertEquals('+A-A', $returnedText);
    }

}
