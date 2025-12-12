<?php

use PHPUnit\Framework\TestCase;
use Pina\Legacy\RequestHandler;

class RequestTest extends TestCase
{
    public function testHandlerInput()
    {
        $data = [
            'name' => 'value',
            'key' => 'other-value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                    'deepest2' => 'something2',
                ],
                'deeper2' => [
                    'deepest' => 'something3',
                    'deepest3' => 'something4',
                ]
            ]
        ];
        
        $request = new RequestHandler('resource', 'get', $data);
        
        $this->assertEquals('value', $request->input('name'));
        $this->assertEquals('something', $request->input('deep.deeper.deepest'));
        $this->assertEquals(['deepest' => 'something', 'deepest2' => 'something2'], $request->input('deep.deeper'));
        
        $this->assertEquals(['something', 'something3'], $request->input('deep.*.deepest'));
        $this->assertEquals(['something', 'something2', 'something3', 'something4'], $request->input('deep.*.*'));
        
        $this->assertEquals(true, $request->has('name'));
        $this->assertEquals(false, $request->has('not-name'));
        $this->assertEquals(true, $request->has('deep.deeper'));
        $this->assertEquals(true, $request->has('deep.deeper.deepest'));
        
        $this->assertEquals(true, $request->has('deep.*.deepest'));
        $this->assertEquals(false, $request->exists('deep.*.deepest'));
        
        $this->assertEquals(true, $request->exists('name'));
        $this->assertEquals(false, $request->exists('not-name'));
        $this->assertEquals(true, $request->exists('deep.deeper'));
        $this->assertEquals(true, $request->exists('deep.deeper.deepest'));
        
        $this->assertEquals($data, $request->all());
        
        $this->assertEquals(['name' => 'value', 'key' => 'other-value'], $request->only('name', 'key'));
        $this->assertEquals(['name' => 'value', 'key' => 'other-value'], $request->only(['name', 'key']));
        
        $this->assertEquals([
            'name' => 'value',
            'deep' => [
                'deeper' => [
                    'deepest' => 'something',
                ],
            ]
        ], $request->only('name', 'deep.deeper.deepest'));
    }
}