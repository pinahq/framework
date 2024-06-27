<?php

class TestEventHandler
{

    protected $key = '';

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function handle($payload)
    {
        global $testEventBuffer;
        $testEventBuffer = $testEventBuffer . $this->key . $payload;
    }

}
