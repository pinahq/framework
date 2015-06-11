<?php

namespace Pina;

class App
{
    
    private static $app = false;
    private static $config = false;

    public static function apps()
    {
        return self::$config['apps'];
    }
    
    public static function init($env, $configPath)
    {
        self::env($env);
        
        Config::initPath($configPath);
        self::$config = Config::load('app');
        
        Language::init();
        
        mb_internal_encoding(self::$config['charset']);
        mb_regex_encoding(self::$config['charset']);

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set(self::$config['timezone']);
        }

    }
    
    public static function run()
    {
        if (!Site::init(!empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '')) {
            @header('HTTP/1.1 404 Not Found');
            exit;
        }

        $method = Core::getRequestMethod();
        if (!in_array($method, array('get', 'put', 'delete', 'post'))) {
            @header("HTTP/1.1 501 Not Implemented");
            exit;
        }

        $data = Core::getRequestData();
        if (empty($data[$method]) && !in_array($_SERVER['REQUEST_URI'], array($_SERVER['SCRIPT_NAME'], "", "/"))) {
            $data[$method] = $_SERVER['REQUEST_URI'];
        }

        $resource = Core::getResource($data, $method);

        $staticFolders = array('/cache/', '/static/', '/uploads/', '/vendor/');
        foreach ($staticFolders as $folder) {
            if (strncasecmp($resource, $folder, strlen($folder)) === 0) {
                @header('HTTP/1.1 404 Not Found');
                exit;
            }
        }

        App::set(App::parse($resource));
        Module::init();
        Core::resource($resource);

        $response = Response\Factory::get($resource, $method);
        if (empty($response)) {
            @header('HTTP/1.1 406 Not Acceptable');
            exit;
        }

        Request::init($response, $data);
        echo Request::run($resource, $method);
    }
    
    public static function path()
    {        
        return self::$config['path'];
    }

    public static function uploads()
    {
        return self::$config['uploads'];
    }
    
    public static function charset()
    {
        return self::$config['charset'];
    }
    
    public static function templaterCache()
    {
        return self::$config['templater']['cache'];
    }
    
    public static function templaterCompiled()
    {
        return self::$config['templater']['compiled'];
    }


    public static function env($env = '')
    {
        static $item = false;

        if (!empty($env) && empty($item)) {
            $item = $env;
        }

        return $item;
    }

    public static function set($app = '')
    {
        if (!empty($app) && (empty(self::$app) || self::env() == 'test')) {
            self::$app = $app;
        }
    }
    
    public static function get()
    {
        return self::$app;
    }

    public static function parse(&$resource)
    {
        $resource = ltrim($resource, '/');
        foreach (self::$config['apps'] as $app => $prefix) {
            if (strpos($resource, $prefix . '/') === 0) {
                $resource = substr($resource, strlen($prefix . '/'));
                return $app;
            }
        }
        return 'frontend';
    }
    
    public static function canUseResources()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $useResources = false;
        if (!empty($_SERVER['DOCUMENT_URI'])) {
            $useResources = strpos($_SERVER['REQUEST_URI'], $_SERVER['DOCUMENT_URI']) !== 0;   
        } else {
            $useResources = strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) !== 0;
        }
        return $useResources;
    }
    
    public static function link($pattern, $params = array())
    {
        $resource = Url::resource($pattern, $params);
        unset($params['get']);
        $ps = '';
        foreach ($params as $k => $v)
        {
            if (strpos($pattern.'/', ':'.$k.'/') === false) {
                if (!empty($ps)) {
                    $ps .= '&';
                }
                $ps .= $k.'='.$v;
            }
        }

        $prefix = '';
        $app = !empty($params['app'])?$params['app']:self::get();
        if (!empty(self::$config['apps'][$app])) $prefix = self::$config['apps'][$app]."/";
        
        $useResources = self::canUseResources();
        
        $url = '';
        if (!$useResources) {
            $url .= '/pina.php?get=';
        } else {
            $url .= '/';
        }
        $url .= $prefix.ltrim($resource, '/');
        if (!$useResources) {
            $url .= !empty($ps)?('&'.$ps):'';
        } else {
            $url .= !empty($ps)?('?'.$ps):'';
        }
        if (!empty($params['anchor'])) {
            $url .= "#" . $params["anchor"];
        }

        return $url;
    }
    

}