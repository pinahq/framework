<?php

namespace Pina\Http;

class Url
{
    protected $url = '';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function __toString()
    {
        return $this->url;
    }

}