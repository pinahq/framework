<?php

namespace Pina\Http;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{

    /** @var Location */
    protected $base;

    /** @var Location */
    protected $location;

    /** @var array */
    protected $context = [];

    public function setLocation(Location $base, Location $location)
    {
        $this->base = $base;
        $this->location = $location;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function getBaseLocation(): Location
    {
        return $this->base ?? new Location('/');
    }

    public function getLocation(): Location
    {
        return $this->location ?? new Location('/');
    }

    public function filters()
    {
        $filters = clone $this->query;
        return $this->fillBagWithContext($filters);
    }

    public function context()
    {
        return $this->fillBagWithContext(new InputBag());
    }

    protected function fillBagWithContext(InputBag $bag)
    {
        $key = 'pid';
        foreach ($this->context as $context) {
            $bag->add([$context => $this->attributes->get($key)]);
            $key = 'p' . $key;
        }
        return $bag;

    }

}