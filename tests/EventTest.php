<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\Event;

require __DIR__ . '/TestEventHandler.php';
require __DIR__ . '/TestEventQueue.php';

class EventTest extends TestCase
{

    public function testEvents()
    {
        global $testEventBuffer;
        
        $testEventBuffer = '';

        $module = new \Pina\Module;

        Event::subscribe($module, 'order.placed', 'order.low', \Pina\Event::PRIORITY_LOW);
        Event::subscribeSync($module, 'order.placed', 'order.high', \Pina\Event::PRIORITY_HIGH);
        Event::subscribe($module, 'order.placed', 'order.norm', \Pina\Event::PRIORITY_NORMAL);
        $this->assertEquals(['Pina::order.low', 'Pina::order.high', 'Pina::order.norm'], App::events()->getHandlerKeys());

        $this->assertEquals('Pina::order.low', App::events()->getHandler('Pina::order.low')->getKey());

        Event::trigger('order.placed', '1');
        Event::trigger('order.placed', '2');
        $this->assertEquals('', $testEventBuffer);
    }
    
    public function testSync()
    {
        global $testEventBuffer;

        $testEventBuffer = '';

        $events = new \Pina\Events\EventManager();

        $events->subscribe('order.placed', new \TestEventHandler('low'), \Pina\Event::PRIORITY_LOW);
        $events->subscribeSync('order.placed', new \TestEventHandler('high'), \Pina\Event::PRIORITY_HIGH);
        $events->subscribe('order.placed', new \TestEventHandler('norm'), \Pina\Event::PRIORITY_NORMAL);
        $this->assertEquals(['low', 'high', 'norm'], $events->getHandlerKeys());

        $this->assertEquals('low', $events->getHandler('low')->getKey());

        $events->trigger('order.placed', '1');
        $events->trigger('order.placed', '2');
        $this->assertEquals('high1norm1low1high2norm2low2', $testEventBuffer);
    }

    public function testAsync()
    {
        App::container()->share(\Pina\EventQueueInterface::class, \TestEventQueue::class);

        global $testEventBuffer;

        $testEventBuffer = '';
        $events = new \Pina\Events\EventManager();
        $events->subscribe('order.placed', new \TestEventHandler('low'), \Pina\Event::PRIORITY_LOW);
        $events->trigger('order.placed', '2');
        $this->assertEquals('', $testEventBuffer);
        $queue = App::container()->get(\Pina\EventQueueInterface::class);
        $queue->work();
        $this->assertEquals('low2', $testEventBuffer);
    }

}
