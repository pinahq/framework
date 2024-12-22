<?php

namespace Pina\Http;

use Pina\App;
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
        return $this->base ?? App::baseUrl();
    }

    public function getLocation(): Location
    {
        return $this->location ?? App::baseUrl();
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
            //опираемся на новую логику без pid, ppid и т.д., сохраняя совместимость
            $value = $this->attributes->has($context) ? $this->attributes->get($context) : $this->attributes->get($key);
            $bag->add([$context => $value]);
            $key = 'p' . $key;
        }
        return $bag;

    }

}