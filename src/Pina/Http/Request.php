<?php

namespace Pina\Http;

use Pina\App;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{

    /** @var Location */
    protected Location $base;

    /** @var Location */
    protected Location $location;

    public function setLocation(Location $base, Location $location)
    {
        $this->base = $base;
        $this->location = $location;
    }

    public function getBaseLocation(): Location
    {
        return $this->base ?? App::location();
    }

    public function getLocation(): Location
    {
        return $this->location ?? App::location();
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