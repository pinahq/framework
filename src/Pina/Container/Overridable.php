<?php

namespace Pina\Container;

use Pina\App;

class Overridable
{
    /**
     * @return static
     */
    public static function make()
    {
        return App::make(static::class);
    }
}