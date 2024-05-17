<?php

namespace Pina\Router;

use Pina\Access;
use Pina\Http\Endpoint;
use Pina\Http\Request;
use Pina\Url;

class Route
{

    protected $pattern = '';
    protected $endpoint = '';
    protected $context = [];

    public function __construct(string $pattern, string $class, array $context = [])
    {
        $this->pattern = $pattern;
        $this->endpoint = $class;
        $this->context = $context;
    }

    public function getController()
    {
        return Url::controller($this->pattern);
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getContext()
    {
        return $this->context;
    }
    
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function makeEndpoint(Request $request): Endpoint
    {
        $cl = $this->endpoint;
        return new $cl($request);
    }

    public function permit($groups)
    {
        Access::permit($this->pattern, $groups);
        return $this;
    }


}