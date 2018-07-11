<?php

namespace Pina;

use Pina\Container\Container;

class App
{

    private static $config = false;
    private static $layout = null;
    private static $container = null;
    private static $supportedMimeTypes = ['text/html', 'application/json', '*/*'];
    private static $forcedMimeType = null;

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

        self::$container = new Container;
        if (isset(self::$config['depencies']) && is_array(self::$config['depencies'])) {
            foreach (self::$config['depencies'] as $key => $value) {
                self::$container->set($key, $value);
            }
        }
        if (isset(self::$config['sharedDepencies']) && is_array(self::$config['sharedDepencies'])) {
            foreach (self::$config['sharedDepencies'] as $key => $value) {
                self::$container->share($key, $value);
            }
        }
        if (!self::$container->has(ModuleRegistryInterface::class)) {
            self::$container->share(ModuleRegistryInterface::class, ModuleRegistry::class);
        }
    }

    public static function container()
    {
        return self::$container;
    }

    public static function run()
    {
        if (self::host() != Input::getHost()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . App::link($_SERVER['REQUEST_URI']));
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

        $mime = App::negotiateMimeType();
        if (empty($mime)) {
            @header('HTTP/1.1 406 Not Acceptable');
            exit;
        }

        App::resource($resource);

        $modules = self::$container->get(ModuleRegistryInterface::class);
        $modules->boot('http');

        $resource = DispatcherRegistry::dispatch($resource);

        $handler = new RequestHandler($resource, $method, $data);

        if (!CSRF::verify($handler->controller(), $data)) {
            @header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $defaultLayout = App::getDefaultLayout();
        if ($defaultLayout) {
            $handler->setLayout($defaultLayout);
        }

        Request::push($handler);
        $response = Request::run();
        $response->send();
    }

    public static function setDefaultLayout($layout)
    {
        self::$layout = $layout;
    }

    public static function getDefaultLayout()
    {
        return self::$layout;
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
        return self::scheme() . "://" . self::host() . "/";
    }

    public static function scheme()
    {
        return isset(self::$config['scheme']) ? self::$config['scheme'] : Input::getScheme();
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

    public static function version()
    {
        return isset(self::$config['version']) ? self::$config['version'] : '';
    }

    public static function env($env = '')
    {
        static $item = false;

        if (!empty($env) && empty($item)) {
            $item = $env;
        }

        return $item;
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

    public static function link($pattern, $params = array())
    {
        $url = self::baseUrl();
        if (Input::isScript() && !empty(self::$config['allow_script_url'])) {
            $url .= 'index.php?action=';
        }

        $resource = Route::resource($pattern, $params);
        $ps = self::getParamsString($pattern, $params);

        $url .= ltrim($resource, '/');
        $url .= !empty($ps) ? ('?' . $ps) : '';

        if (!empty($params['anchor'])) {
            $url .= "#" . $params["anchor"];
        }

        return $url;
    }

    public static function forceMimeType($mime)
    {
        static::$forcedMimeType = $mime;
    }

    public static function negotiateMimeType()
    {
        if (!empty(static::$forcedMimeType)) {
            return static::$forcedMimeType;
        }

        $acceptTypes = [];

        $accept = strtolower(str_replace(' ', '', isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : ''));
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
            $q = 1;
            if (strpos($a, ';q=')) {
                list($a, $q) = explode(';q=', $a);
            }
            $acceptTypes[$a] = $q;
        }
        arsort($acceptTypes);

        if (!static::$supportedMimeTypes) {
            return $acceptTypes;
        }

        $supported = array_map('strtolower', static::$supportedMimeTypes);

        foreach ($acceptTypes as $mime => $q) {
            if ($q && in_array($mime, $supported)) {
                return $mime;
            }
        }
        return 'text/html';
    }

    public static function createResponseContent($results, $controller, $action)
    {
        $mime = static::negotiateMimeType();
        switch ($mime) {
            case 'application/json':
            case 'text/json':
                return new JsonContent($results);
        }

        $template = 'pina:' . $controller . '!' . $action . '!' . Request::input('display');
        return new TemplaterContent($results, $template, Request::isExternalRequest());
    }

    public static function getUpgrades()
    {
        $upgrades = [];
        $triggers = [];
        App::walkClasses('Gateway', function($gw) use (&$upgrades, &$triggers) {
            $upgrades = array_merge($upgrades, $gw->getUpgrades());
            $triggers = array_merge($triggers, $gw->getTriggers());
        });

        $upgrades = array_merge($upgrades, TableDataGatewayTriggerUpgrade::getUpgrades($triggers));

        return $upgrades;
    }

    public static function walkClasses($type, $callback)
    {
        $paths = self::$container->get(ModuleRegistryInterface::class)->getPaths();
        $suffix = $type . '.php';
        $suffixLength = strlen($suffix);
        $r = [];
        foreach ($paths as $ns => $path) {
            $files = array_filter(scandir($path), function($s) use ($suffix, $suffixLength) {
                return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
            });

            foreach ($files as $file) {
                $className = $ns . '\\' . pathinfo($file, PATHINFO_FILENAME);
                $c = new $className;
                $callback($c);
            }
        }
    }

}

function __($string)
{
    return Language::translate($string);
}
