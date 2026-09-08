<?php

namespace Pina\Container;

use Pina\App;

trait ContainerItemTrait
{
    /**
     * @return static
     */
    public static function make()
    {
        return App::make(static::class);
    }
}