<?php

namespace Pina;

class Language
{

    private static $code = null;
    private static $data = [];
    
    public static function init()
    {
        $config = Config::load('language');
        static::$code = isset($config['default']) ? $config['default'] : false;
    }

    public static function code($code = '')
    {
        if ($code == '') {
            return static::$code;
        }

        $oldCode = static::$code;

        static::$code = $code;

        return $oldCode;
    }

    public static function translate($string)
    {
        if (!isset(self::$code)) {
            static::init();
        }
        
        if (empty(self::$code)) {
            return '';
        }
        
        $string = trim($string);
        if (empty($string)) {
            return '';
        }
        
        $module = Request::module();
        
        if (!isset(static::$data[static::$code])) {
            static::$data[static::$code] = [];
        }
        
        if (!isset(static::$data[static::$code][$module])) {
            $path = ModuleRegistry::getPath($module);
            $file = $path."/lang/".static::$code.'.php';
            static::$data[static::$code][$module] = file_exists($file) ? include($file) : [];
        }
        
        if (!isset(static::$data[static::$code][$module][$string])) {
            return $string;
        }
        
        return static::$data[static::$code][$module][$string];
    }

    public static function val($key)
    {
        return self::translate($string);
    }

}
