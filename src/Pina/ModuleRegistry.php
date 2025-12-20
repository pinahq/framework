<?php

namespace Pina;

use ArrayIterator;
use IteratorAggregate;

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

    /**
     * @deprecated в пользу метода App::onLoad
     * @param $method
     * @return void
     */
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
            $module->$method();
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


    /**
     * Обходит классы, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public function walkRootClasses($type, $callback)
    {
        $paths = $this->getPaths();
        $suffix = $type . '.php';
        $suffixLength = strlen($suffix);
        foreach ($paths as $ns => $path) {
            $files = array_filter(scandir($path), function ($s) use ($suffix, $suffixLength) {
                return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
            });

            foreach ($files as $file) {
                $className = $ns . '\\' . pathinfo($file, PATHINFO_FILENAME);
                try {
                    $c = new $className;
                    $callback($c);
                } catch (\Throwable $e) {
                }
            }
        }
    }

    /**
     * Обходит классы, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public function walkClasses(string $type, callable $callback)
    {
        $this->walkClassNames($type, function($className) use ($callback) {
            try {
                $c = new $className;
                $callback($c);
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Обходит именна классов, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public function walkClassNames(string $type, callable $callback)
    {
    $paths = $this->getPaths();
        foreach ($paths as $ns => $path) {
            $this->walkClassNamesInPath($ns, $path, $type, $callback);
        }
    }

    public function walkClassNamesInPath(string $ns, string $path, string $type, callable $callback)
    {
        $suffix = $type . '.php';
        $suffixLength = strlen($suffix);
        $allFiles = scandir($path);
        $toWalk = array_filter($allFiles, function ($s) use ($suffix, $suffixLength) {
            return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
        });

        foreach ($toWalk as $file) {
            $className = $ns . '\\' . pathinfo($file, PATHINFO_FILENAME);
            $callback($className);
        }

        $paths = array_filter($allFiles, function ($s) use ($path) {
            return $s[0] >= 'A' && $s[0] <= 'Z' && is_dir($path . '/' . $s);
        });

        foreach ($paths as $file) {
            $this->walkClassNamesInPath(
                $ns . '\\' . pathinfo($file, PATHINFO_FILENAME),
                $path . '/' . $file,
                $type,
                $callback
            );
        }
    }


}
