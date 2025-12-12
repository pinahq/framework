<?php

namespace Pina\Legacy;

use Pina\App;
use Pina\Url;

class Route
{

    public static function router(): ScriptRouter
    {
        return App::load(ScriptRouter::class);
    }


    /**
     * @deprecated
     */
    public static function context($key = null, $value = null)
    {
    }
    
    public static function resource($pattern, $parsed)
    {
        return Url::resource($pattern, $parsed, Request::resource());
    }

}
