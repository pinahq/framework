<?php

namespace Pina;

interface ModuleRegistryInterface
{

    public function boot($method = null);

    public function get($ns);
    
    public function getNamespaces();
        
    public function getPaths();
    
}
