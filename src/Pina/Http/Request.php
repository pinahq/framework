<?php

namespace Pina\Http;

use Pina\App;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{

    /** @var Location */
    protected $base;

    /** @var Location */
    protected $location;

    public function setLocation(Location $base, Location $location)
    {
        $this->base = $base;
        $this->location = $location;
    }

    public function getBaseLocation(): Location
    {
        return $this->base ?? App::baseUrl();
    }

    public function getLocation(): Location
    {
        return $this->location ?? App::baseUrl();
    }

    public function filters()
    {
        $filters = clone $this->query;
        foreach ($this->attributes as $key => $value) {
            $filters->add([$key => $value]);
        }
        return $filters;
    }

}