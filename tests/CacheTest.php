<?php

use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{

    public function testCommand()
    {
        $cache = new \Pina\Cache\Cache();
        $key = 'query:SELECT value FROM test WHERE id=1';
        $value = 100;
        $cache->set($key, $value, 1);

        $this->assertEquals(100, $cache->get($key));
        $this->assertEquals(true, $cache->has($key));
        sleep(2);
        $this->assertEquals(false, $cache->has($key));

    }

}