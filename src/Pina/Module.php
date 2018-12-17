<?php

namespace Pina;

class Module implements ModuleInterface
{

    public function getPath()
    {
        return __DIR__;
    }
    
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getTitle()
    {
        return 'Framework';
    }
    
    public function http()
    {
        return [];
    }
    
    public function cli()
    {
        return [
            'system'
        ];
    }
    
    public function boot()
    {
        
    }

}
