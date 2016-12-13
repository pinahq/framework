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
            __NAMESPACE__
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
                self::$paths[$ns] = str_replace('Pina\\Modules\\', App::path()."/default/Modules/", $ns);
                continue;
            }
            
            self::$paths[$ns] = call_user_func(array($className, 'getPath'));
        }
    }

    public static function initModules()
    {       
        $app = App::get();
        
        foreach (self::$paths as $base) {
            $path = $base.'/'.$app.'/init.php';
            if (is_file($path)) {
                include_once $path;
            }
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