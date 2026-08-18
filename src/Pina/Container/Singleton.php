<?php

namespace Pina\Container;

use Pina\App;

class Singleton
{
    /**
     * @return static
     */
    public static function load()
    {
        return App::load(static::class);
    }
}