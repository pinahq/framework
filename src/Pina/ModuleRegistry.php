<?php

namespace Pina;

class ModuleRegistry
{

    private static $registry = [];
    private static $paths = [];
    
    public static function init()
    {
        $config = Config::load('modules');
        $default_modules = [
            __NAMESPACE__
        ];
        if (!empty($config['default']) && is_array($config['default'])) {
            $default_modules = array_merge($default_modules, $config['default']);
        }
        
        $modules = array();

        $enabled_modules = $default_modules;
        
        Access::reset();
        self::$registry = [];
        foreach ($enabled_modules as $ns) {
            $className = $ns.'\\Module';
            self::$registry[$ns] = new $className;
        }
    }

    public static function initModules($method = null)
    {       
        foreach (self::$registry as $ns => $module) {
            if ($method && !method_exists($module, $method)) {
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
        
        foreach (self::$registry as $module) {
            $module->boot();
        }
    }
    
    public static function add($ns, $title)
    {
        if (empty($ns) || empty($title)) {
            return false;
        }
        
        $moduleId = ModuleGateway::instance()->whereBy('module_namespace', $ns)->value('module_id');
        if (!$moduleId) {
            $moduleId = ModuleGateway::instance()->insertGetId(array(
                'module_title' => $title,
                'module_namespace' => $ns,
                'module_enabled' => 'Y',
            ));
        }
        
        return $moduleId;
    }

    public static function isActive($module)
    {
        return isset(self::$registry[$module]);
    }
    
    public static function get($ns)
    {
        if (!isset(self::$registry[$ns])) {
            return false;
        }
        
        return self::$registry[$ns];
    }
    
    public static function getPaths()
    {
        $paths = [];
        foreach (self::$registry as $ns => $module) {
            $paths[$ns] = $module->getPath();
        }
        return $paths;
    }
    
    public static function walkClasses($type, $callback)
    {
        $paths = self::getPaths();
        $suffix = $type.'.php';
        $suffixLength = strlen($suffix);
        $r = array();
        foreach ($paths as $ns => $path) {
            $files = array_filter(scandir($path), function($s) use ($suffix, $suffixLength) {
                return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
            });

            foreach ($files as $file) {
                $className = $ns.'\\'.pathinfo($file, PATHINFO_FILENAME);
                $c = new $className;
                $callback($c);
            }
        }
    }
}
