<?php

use Pina\Arr;

class ArrTest extends PHPUnit_Framework_TestCase
{

    public function testDiff()
    {
        $a1 = [1, 2, 2, 3];
        $a2 = [2, 3];
        $this->assertEquals([1, 2], Arr::diff($a1, $a2));
        
        $a1 = [];
        $a2 = [2, 3];
        $this->assertEquals([], Arr::diff($a1, $a2));
        
        $a1 = [1, 2, 2, 3];
        $a2 = [];
        $this->assertEquals([1, 2, 2, 3], Arr::diff($a1, $a2));
        
        $a1 = ["hello!", "hello!", 2, 2, 3];
        $a2 = ["hello!"];
        $this->assertEquals(["hello!", 2, 2, 3], Arr::diff($a1, $a2));
    }

}
