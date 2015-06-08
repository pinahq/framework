<?php

namespace Pina;

class Config
{

    private static $config = array();
    private static $path = false;
    private static $temp = array();
    private static $data = array();
    private static $loaded = array();

    private static function init($siteId = false)
    {
        if (empty($siteId)) {
            $siteId = Site::id();
        }
        
        if (empty(self::$config))
        {
            self::$config = self::load('config');
        }

        $table = new self::$config['table'];
        $lines = $table->whereBy('site_id', $siteId)->get();

        self::$data = array();
        foreach ($lines as $k => $v) {
            self::$data[$siteId][$v['module_key']][$v['config_key']] = $v['config_value'];
        }
        
        self::$loaded[$siteId] = true;
    }
    
    public static function set($module_key, $key, $value, $siteId = false)
    {
        if (empty($siteId)) {
            $siteId = Site::id();
        }
        
        if (empty(self::$loaded[$siteId])) {
            self::init($siteId);
        }
        
        self::$data[$module_key][$key] = $value;

        $table = new self::$config['table'];     
        $table->put(array(
            'site_id' => $siteId,
            'module_key' => $module_key,
            'module_value' => $value
        ));
    }
    
    public static function setTemporary($module_key, $key, $value, $siteId = false)
    {
        if (empty($siteId)) {
            $siteId = Site::id();
        }
        self::$temp[$siteId][$module_key][$key] = $value;
    }

    public static function get($module_key, $key, $siteId = false)
    {
        if (empty($siteId)) {
            $siteId = Site::id();
        }

        if (isset(self::$temp[$siteId][$module_key][$key])) {
            return self::$temp[$siteId][$module_key][$key];
        }
        
        if (empty(self::$loaded[$siteId])) {
            self::init($siteId);
        }

        if (!isset(self::$data[$siteId][$module_key][$key])) {
            return false;
        }

        return self::$data[$siteId][$module_key][$key];
    }
    
    
    public static function initPath($path)
    {
        if (!empty(self::$path)) return;

        self::$path = $path;
    }

    public static function load($s)
    {
        if (empty(self::$path)) {
            return false;
        }
        return include self::$path . "/" . $s . ".php";
    }

}
