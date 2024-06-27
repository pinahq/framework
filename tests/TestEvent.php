<?php

class TestEvent extends \Pina\Events\Event
{
    protected $text = '';

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function queueable(): bool
    {
        return true;
    }

    public function serialize(): array
    {
        return [$this->text];
    }


    public function getText()
    {
        return $this->text;
    }
}