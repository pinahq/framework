<?php

namespace Pina;

class Request
{

    protected static $response = false;
    protected static $stack = array();

    public static function init($response, $data)
    {
        self::$response = $response;
        self::$stack = array();
        
        if (is_array($data)) {
            array_push(self::$stack, $data);
        } else {
            array_push(self::$stack, array('__raw' => $data));
        }
    }

    public static function internal($resource, $method, $data = array())
    {
        if (!empty($data["mode"])) {
            self::result("mode", $data["mode"]);
        }
        if (!empty($data["title"])) {
            self::result("title", $data["title"]);
        }

        array_push(self::$stack, $data);
        $r = self::run($resource, $method);
        array_pop(self::$stack);
        return $r;
    }

    public static function middleware($resource, $method, $data = array())
    {
        $oldResponse = self::$response;
        self::$response = new Response\MiddlewareResponse();

        array_push(self::$stack, $data);
        $r = self::run($resource, $method);
        array_pop(self::$stack);
        self::$response = $oldResponse;

        return $r;
    }

    public static function set($name, $value)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        self::$stack[$top][$name] = $value;
    }
    
    public static function match($pattern)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }
        
        if (empty(self::$stack[$top]['__resource'])) {
            return;
        }
        
        $resource = Url::trim(self::$stack[$top]['__resource']);
        $pattern = Url::trim($pattern);
        $parsed = Url::parse($resource, $pattern);
        foreach ($parsed as $k => $v) {
            self::set($k, urldecode($v));
        }
    }

    // выполнение контроллера сопровождается предупреждением
    public static function warning($message, $subject = '')
    {
        self::$response->warning($message, $subject);
    }

    // выполнение контроллера сопровождается ошибкой.
    // $message - текст ошибки
    // $subject - код ошибки
    public static function error($message = "", $subject = '')
    {
        self::$response->error($message, $subject);
    }

    // проверяет встречались ли ошибки при выполнении
    // запроса и завершает выполнение в случае
    // найденных ошибок
    public static function trust()
    {
        self::$response->trust();
    }

    // выполнение контроллера прерывается ошибкой
    // $message - текст ошибки
    // $subject - код ошибки
    public static function stop($message = "", $subject = '')
    {
        self::$response->stop($message, $subject);
    }

    // получаем параметр запроса к контроллеру по его названию
    public static function param($name)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return null;
        }

        if (!isset(self::$stack[$top][$name])) {
            return null;
        }

        return self::$stack[$top][$name];
    }

    public static function params($ps = "")
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return array();
        }

        if (empty($ps)) {
            return self::$stack[$top];
        }

        if (!is_array($ps)) {
            $ps = explode(' ', $ps);
        }

        $res = array();
        if (is_array($ps)) {
            foreach ($ps as $p) {
                if (!isset(self::$stack[$top][$p])) {
                    continue;
                }

                $res[$p] = self::$stack[$top][$p];
            }
        }
        return $res;
    }

    public static function raw() 
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return '';
        }
        
        if (!empty(self::$stack[$top]['__raw'])) {
            return self::$stack[$top]['__raw'];
        }
        
        return file_get_contents('php://input');
    }

    public static function filterSub($fs, &$data)
    {
        foreach ($data as $k => $v) {
            if (is_array($data[$k])) {
                self::filterSub($fs, $data[$k]);
                continue;
            }

            foreach ($fs as $f) {
                if (empty($f)) {
                    continue;
                }
                $data[$k] = call_user_func($f, $data[$k]);
            }
        }
    }

    public static function filter($fs, $ps)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        if (!is_array($ps)) {
            $ps = explode(' ', $ps);
        }

        if (!is_array($fs)) {
            $fs = explode(' ', $fs);
        }

        foreach ($ps as $p) {
            if (empty($p)) {
                continue;
            }

            if (!isset(self::$stack[$top][$p])) {
                continue;
            }

            if (isset(self::$stack[$top][$p]) && is_array(self::$stack[$top][$p])) {
                self::filterSub($fs, self::$stack[$top][$p]);
                continue;
            }

            $data = '';
            if (isset(self::$stack[$top][$p])) {
                $data = self::$stack[$top][$p];
            }

            foreach ($fs as $f) {
                if (empty($f)) {
                    continue;
                }

                self::$stack[$top][$p] = $data = call_user_func($f, $data);
            }
        }
    }

    public static function filterAll($clean_functions)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        if (empty(self::$stack[$top]) || !is_array(self::$stack[$top])) {
            return;
        }
        $fs = explode(' ', $clean_functions);
        foreach (self::$stack[$top] as $k => $v) {
            if (is_array(self::$stack[$top][$k])) {
                self::filterSub($fs, self::$stack[$top][$k]);
                continue;
            }

            foreach ($fs as $f) {
                if (empty($f)) {
                    continue;
                }
                self::$stack[$top][$k] = call_user_func($f, self::$stack[$top][$k]);
            }
        }
    }

    // связываем результат выполнения контроллера
    public static function result($name, $value)
    {
        self::$response->result($name, $value);
    }

    public static function isAvailable($module, $resource)
    {
        if (App::env() === "cli") {
            return true;
        }
        
        return ModuleRegistry::isActive($module) && Access::isHandlerPermitted($resource);
    }
    
    public static function module()
    {
        $top = count(self::$stack) - 1;
        return self::$stack[$top]['__module'];
    }

    protected static function runHandler($handler)
    {
        if (is_file($handler . ".php")) {
            include $handler . ".php";
        } else {
            self::notFound();
        }
    }

    protected static function runInternalHandler($handler)
    {
        $path = App::path();
        if (!is_file($handler . ".php")) {
            return false;
        }

        try {
            include $handler . ".php";
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    public static function run($resource, $method)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return '';
        }
        
        self::$stack[$top]["__resource"] = $resource;

        $isExternal = $top == 0;
        
        list($controller, $action, $data) = Url::route($resource, $method);
        
        $module = Route::owner($controller);
        
        if (empty($module)) {
            header('HTTP/1.1 404 Not Found');
            return self::run('errors/not-found', 'get');
        }
        
        self::$stack[$top]["__module"] = $module;
        
        if (!self::isAvailable($module, $resource)) {
            if ($isExternal && $resource != 'errors/access-denied') {
                return self::run('errors/access-denied', 'get');
            } else {
                return '';
            }
        }

        self::$stack[$top] = array_merge(self::$stack[$top], $data);
        $handler = Url::handler($controller, $action);
        
        $path = ModuleRegistry::getPath($module);

        if ($isExternal) {
            Middleware::processBefore($resource, $action, self::$stack[$top], $method);
            self::runHandler($path . '/' . $handler);
            Middleware::processAfter($resource, $action, self::$stack[$top], $method);
            
            if (self::$response->code) {
                header(self::$response->code);
            }

        } else {
            if (!self::runInternalHandler($path . '/' . $handler)) {
                return '';
            }
        }

        if ($isExternal && self::$response->code == '404 Not Found' && $resource != 'errors/not-found') {
            return self::run('errors/not-found', 'get');
        }
        
        if ($isExternal && self::$response->code == '403 Forbidden' && $resource != 'errors/forbidden') {
            return self::run('errors/forbidden', 'get');
        }

        return self::$response->fetch($controller.'!'.$action.'!'.(isset(self::$stack[$top]['display'])?self::$stack[$top]['display']:''), $isExternal);
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(self::$response, $name)) {
            call_user_func_array(array(self::$response, $name), $arguments);
        } else {
            die('method Request::'.$name.' not exists');
        }
    }

}
