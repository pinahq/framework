<?php

namespace Pina;

class ModuleRegistry
{

    private static $enabled_modules = array();
    private static $default_modules = array();
    private static $paths = array();

    public static function init()
    {        
        $config = Config::load('modules');
        self::$default_modules = array(
            'Pina'
        );
        if (!empty($config['default']) && is_array($config['default'])) {
            self::$default_modules = array_merge(self::$default_modules, $config['default']);
        }
        
        $modules = array();

        $enabled_modules = array();
        self::$enabled_modules = array_merge(self::$default_modules, $enabled_modules);
        
        $app = App::get();
        
        Access::reset();
        self::$paths = array();
        foreach (self::$enabled_modules as $ns) {
            $className = $ns.'\\Module';
            if (!class_exists($className)) {
                continue;
            }
            
            self::$paths[$ns] = call_user_func(array($className, 'getPath'));
        }
        
        foreach (self::$paths as $base) {
            $path = $base.'/'.$app.'/init.php';
            if (is_file($path)) {
                include_once $path;
            }
        }
        
    }

    public static function isActive($module)
    {
        return in_array($module, self::$enabled_modules);
    }
    
    public static function getPath($module)
    {
        if (!isset(self::$paths[$module])) {
            return false;
        }
        
        return self::$paths[$module];
    }
    
    public static function getPaths()
    {
        return self::$paths;
    }
}
