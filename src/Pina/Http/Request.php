<?php

namespace Pina\Http;

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
        return $this->base ?? new Location('/');
    }

    public function getLocation(): Location
    {
        return $this->location ?? new Location('/');
    }

}