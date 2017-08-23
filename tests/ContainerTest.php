<?php

use PHPUnit\Framework\TestCase;
use Pina\Container\Container;
use Pina\Container\NotFoundException;

class ContainerTest extends TestCase
{

    public function testException()
    {
        $this->expectException(NotFoundException::class);
        
        $container = new Container;
        $notFound = $container->get('id');
    }
    
    public function test()
    {
        $container = new Container;
        $container->set('container', Container::class);
        $this->assertTrue($container->get('container') instanceof Container);
        $this->assertFalse($container->get('container') instanceof \Pina\Paging);
        
        $paging = new \Pina\Paging(1, 10);
        $container->set('paging', $paging);
        $this->assertEquals(1, $container->get('paging')->getCurrent());
        
        $paging = new \Pina\Paging(1, 10);
        $container->share('paging', $paging);
        $this->assertEquals(1, $container->get('paging')->getCurrent());
    }
    
}

