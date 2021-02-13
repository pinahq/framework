<?php

namespace Pina;

class ModuleRegistry
{

    protected $registry = [];

    public function __construct()
    {
        Access::reset();
        $this->registry = [];

        $this->load(Module::class);
        
        $modules = Config::load('modules');
        if (is_array($modules)) {
            foreach ($modules as $ns) {
                $this->load($ns . '\\Module');
            }
        }
    }

    public function load($module)
    {
        if (isset($this->registry[$module])) {
            return;
        }
        $this->registry[$module] = new $module;
    }

    public function boot($method = null)
    {
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

    public function get($module)
    {
        if (!isset($this->registry[$module])) {
            return false;
        }

        return $this->registry[$module];
    }

    public function getPaths()
    {
        $paths = [];
        foreach ($this->registry as $cn => $module) {
            $l = strlen($cn) - strlen("\\Module");
            $ns = substr($cn, 0, $l);
            $paths[$ns] = $module->getPath();
        }
        return $paths;
    }

}
