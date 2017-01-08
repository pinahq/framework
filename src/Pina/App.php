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

        Config::init($configPath);
        self::$config = Config::load('app');

        mb_internal_encoding(self::$config['charset']);
        mb_regex_encoding(self::$config['charset']);

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set(self::$config['timezone']);
        }
    }

    public static function run()
    {
        if (self::host() != Input::getHost()) {
            @header('HTTP/1.1 404 Not Found');
            exit;
        }

        $method = Input::getMethod();
        if (!in_array($method, array('get', 'put', 'delete', 'post'))) {
            @header("HTTP/1.1 501 Not Implemented");
            exit;
        }

        $data = Input::getData();
        if (empty($data[$method]) && !in_array($_SERVER['REQUEST_URI'], array($_SERVER['SCRIPT_NAME'], "", "/"))) {
            $data[$method] = $_SERVER['REQUEST_URI'];
        }

        $resource = Input::getResource();

        //TODO: get these paths based on config
        $staticFolders = array('cache/', 'static/', 'uploads/', 'vendor/');
        foreach ($staticFolders as $folder) {
            if (strncasecmp($resource, $folder, strlen($folder)) === 0) {
                @header('HTTP/1.1 404 Not Found');
                exit;
            }
        }

        $app = App::parse($resource);
        App::set($app);
        App::resource($resource);
        
        ModuleRegistry::init();
        
        Request::init($data);
        
        ModuleRegistry::initModules();
        
        $resource = DispatcherRegistry::dispatch($resource);
        Request::run($resource, $method);
    }
    
    public static function resource($resource = '')
    {
        static $item = false;

        if (!empty($resource) && empty($item)) {
            $item = $resource;
        }

        return $item;
    }
    
    public static function baseUrl()
    {
        return self::scheme()."://".self::host()."/";
    }
    
    public static function scheme()
    {
        return isset(self::$config['scheme'])?self::$config['scheme']:'http';
    }
    
    public static function host()
    {
        return isset(self::$config['host']) ? self::$config['host'] : Input::getHost();
    }
    
    public static function template()
    {
        return isset(self::$config['template']) ? self::$config['template'] : null;
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
    
    public static function tmp()
    {
        return self::$config['tmp'];
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

    public static function getParamsString($pattern, $params)
    {
        $systemParamKeys = array('get', 'app', 'anchor');

        foreach ($params as $k => $v) {
            if (strpos($pattern . '/', ':' . $k . '/') !== false || in_array($k, $systemParamKeys)) {
                unset($params[$k]);
            }
        }
        
        return http_build_query($params);
    }

    public static function getLinkPrefix($params)
    {
        $prefix = '';
        $app = !empty($params['app']) ? $params['app'] : self::get();
        if (!empty(self::$config['apps'][$app])) {
            $prefix = self::$config['apps'][$app] . "/";
        }
        return $prefix;
    }

    public static function link($pattern, $params = array())
    {
        $url = self::baseUrl();
        if (Input::isScript() && !empty(self::$config['allow_script_url'])) {
            $url .= 'index.php?action=';
        }

        $resource = Route::resource($pattern, $params);
        $prefix = self::getLinkPrefix($params);
        $ps = self::getParamsString($pattern, $params);

        $url .= $prefix . ltrim($resource, '/');
        $url .=!empty($ps) ? ('?' . $ps) : '';

        if (!empty($params['anchor'])) {
            $url .= "#" . $params["anchor"];
        }

        return $url;
    }

}

function __($string)
{
    return Language::translate($string);
}