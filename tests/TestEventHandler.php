<?php

class TestEventHandler implements Pina\Events\EventHandlerInterface
{

    protected $key = '';

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function handle($payload)
    {
        global $testEventBuffer;
        $testEventBuffer = $testEventBuffer . $this->key . $payload;
    }

}
