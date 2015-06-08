<?php

namespace Pina;

class Module
{

    private static $enabled_modules = array();
    private static $default_modules = array();

    public static function init()
    {
        $config = Config::load('modules');
        self::$default_modules = $config['default'];
        
        $db = DB::get();
        
        $modules = array();

        $enabled_modules = array();
        if (!empty($config['table'])) {
            $table = new $config['table'];
            $modules = $table
                ->whereBy('module_enabled', 'Y')
                ->column('module_key');
            $enabled_modules = array_merge($enabled_modules, $modules);
        }

        self::$enabled_modules = array_merge(self::$default_modules, $enabled_modules);
        
        $app = App::get();

        foreach (self::$enabled_modules as $v) {
            $path = App::path() .'/default/Modules/'. $v . '/' . $app . '/init.php';
            if (is_file($path)) {
                include_once $path;
            }
        }

    }

    public static function isActive($module)
    {
        return in_array($module, self::$enabled_modules);
    }
    
    public static function paths($postfix)
    {
        $base = App::path();
        $paths = array();
        foreach (self::$enabled_modules as $k => $v) {
            $paths[] =  $base.'/default/Modules/'. $v . '/' . $postfix;
        }
        return $paths;
    }
}
