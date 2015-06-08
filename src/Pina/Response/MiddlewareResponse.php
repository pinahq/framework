<?php

namespace Pina\Response;

class MiddlewareResponse extends Response
{

    public function fail()
    {
        throw new Exception(join("\n", $this->error_messages));
    }

    public function result($name, $value)
    {
        
    }

    public function fetch($handler = '', $first = true)
    {
    }

}
