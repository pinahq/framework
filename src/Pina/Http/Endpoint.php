<?php

namespace Pina\Http;

class Endpoint
{
    
    /**
     *
     * @var Pina\Http\Request;
     */
    protected $request;
    
    public function __construct()
    {
        $this->request = new Request();
    }
    
    public function setRequest(Request $request) {
        $this->request = $request;
    }
    
}
