<?php

namespace Pina\Container;

use Pina\App;

trait SingletonTrait
{
    /**
     * @return static
     */
    public static function load()
    {
        return App::load(static::class);
    }
}