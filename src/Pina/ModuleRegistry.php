<?php

namespace Pina;

class ModuleRegistry implements ModuleRegistryInterface
{

    protected $registry = [];
    
    public function __construct()
    {
        $config = Config::load('modules');
        $modules = [
            __NAMESPACE__
        ];
        if (is_array($config)) {
            $modules = array_merge($modules, $config);
        }
        
        Access::reset();
        $this->registry = [];
        foreach ($modules as $ns) {
            $className = $ns.'\\Module';
            $this->registry[$ns] = new $className;
        }
    }

    public function boot($method = null)
    {       
        foreach ($this->registry as $module) {
            $module->boot();
        }
        
        foreach ($this->registry as $ns => $module) {
            if (!$method || !method_exists($module, $method)) {
                continue;
            }
            $routes = $module->$method();
            
            if (!is_array($routes)) {
                continue;
            }
            
            foreach ($routes as $route) {
                Route::own($route, $module);
            }
        }
    }

    public function get($ns)
    {
        if (!isset($this->registry[$ns])) {
            return false;
        }
        
        return $this->registry[$ns];
    }
    
    public function getNamespaces()
    {
        return array_keys($this->registry);
    }

    public function getPaths()
    {
        $paths = [];
        foreach ($this->registry as $ns => $module) {
            $paths[$ns] = $module->getPath();
        }
        return $paths;
    }
    
}
