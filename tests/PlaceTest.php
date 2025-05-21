<?php

use PHPUnit\Framework\TestCase;

class PlaceTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testPlace()
    {
        $place = \Pina\App::place('test');
        $place->push(function($a, $b) {
            return $a + $b;
        });
        $place->push(function($a, $b) {
            return $a * $b;
        });


        $merged = \Pina\App::place('test', 2, 3)->make();
        $this->assertEquals('56', $merged);

        $merged = \Pina\App::place('test')->make(2, 3);
        $this->assertEquals('56', $merged);
    }

}