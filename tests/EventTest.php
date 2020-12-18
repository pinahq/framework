<?php

use PHPUnit\Framework\TestCase;
use Pina\App;

require __DIR__.'/TestEventHandler.php';

class EventTest extends TestCase
{

    public function testEvents()
    {
        global $testEventBuffer;
        
        $testEventBuffer = '';
        
        App::events()->subscribe('order.placed', new \TestEventHandler('low'), \Pina\Event::PRIORITY_LOW);
        App::events()->subscribeSync('order.placed', new \TestEventHandler('high'), \Pina\Event::PRIORITY_HIGH);
        App::events()->subscribe('order.placed', new \TestEventHandler('norm'), \Pina\Event::PRIORITY_NORMAL);
        $this->assertEquals(['low', 'high', 'norm'], App::events()->getHandlerKeys());
        
        $this->assertEquals('low', App::events()->getHandler('low')->getKey());
        
        App::events()->trigger('order.placed', '1');
        App::events()->trigger('order.placed', '2');
        $this->assertEquals('high1norm1low1high2norm2low2', $testEventBuffer);
        
    }


}
