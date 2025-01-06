<?php

use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{

    public function testCommand()
    {
        $cache = new \Pina\Cache();
        $key = 'query:SELECT value FROM test WHERE id=1';
        $cache->set($key, 1, 100);

        $this->assertEquals(100, $cache->get($key));
        sleep(2);
        $this->assertEquals(null, $cache->get($key));

        print_r($cache);

    }

}