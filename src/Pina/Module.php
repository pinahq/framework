<?php

namespace Pina;

class Module implements ModuleInterface
{

    static public function getPath()
    {
        return __DIR__;
    }

    static public function getTitle()
    {
        return 'Framework';
    }

}
