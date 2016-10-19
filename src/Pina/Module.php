<?php

namespace Pina;

class Module implements ModuleInterface
{

    public static function getPath()
    {
        return __DIR__;
    }

    public static function getTitle()
    {
        return 'Framework';
    }

}
