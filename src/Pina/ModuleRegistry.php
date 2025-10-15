<?php

namespace Pina;

use IteratorAggregate;
use ArrayIterator;

class ModuleRegistry implements IteratorAggregate
{

    protected $bootOrder = [];
    protected $registry = [];

    public function load($module)
    {
        if (isset($this->registry[$module])) {
            return;
        }
        $this->bootOrder[] = $module;
        $this->registry[$module] = new $module;
    }

    public function loadBefore($module, $mark)
    {
        if (isset($this->registry[$module])) {
            return;
        }
        $positionKey = array_search($mark, $this->bootOrder);
        if ($positionKey === false) {
            $this->load($module);
            return;
        }
        $this->bootOrder = array_merge(
            array_slice($this->bootOrder, 0, $positionKey),
            [$module],
            array_slice($this->bootOrder, $positionKey)
        );
        $this->registry[$module] = new $module;
    }

    /**
     * Итератор по модулям
     * @return ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new ArrayIterator($this->registry);
    }

    public function boot($method = null)
    {
        $this->load(Module::class);

        $modules = Config::load('modules');
        if (is_array($modules)) {
            foreach ($modules as $ns) {
                $this->load($ns . '\\Module');
            }
        }

        foreach ($this->bootOrder as $ns) {
            $module = $this->registry[$ns];
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
            return null;
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
