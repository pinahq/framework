<?php

namespace Pina;

class Request
{

    static protected $response = false;
    static protected $stack = array();

    static public function init($response, $data)
    {
        self::$response = $response;
        self::$stack = array();
        
        if (is_array($data)) {
            array_push(self::$stack, $data);
        } else {
            array_push(self::$stack, array('__raw' => $data));
        }
    }

    static public function internal($resource, $method, $data = array())
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

    static public function middleware($resource, $method, $data = array())
    {
        $oldResponse = self::$response;
        self::$response = new Response\MiddlewareResponse();

        array_push(self::$stack, $data);
        $r = self::run($resource, $method);
        array_pop(self::$stack);
        self::$response = $oldResponse;

        return $r;
    }

    static public function set($name, $value)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }

        self::$stack[$top][$name] = $value;
    }
    
    static public function match($pattern)
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
            self::set($k, $v);
        }
    }

    // выполнение контроллера сопровождается предупреждением
    static public function warning($message, $subject = '')
    {
        self::$response->warning($message, $subject);
    }

    // выполнение контроллера сопровождается ошибкой.
    // $message - текст ошибки
    // $subject - код ошибки
    static public function error($message = "", $subject = '')
    {
        self::$response->error($message, $subject);
    }

    // проверяет встречались ли ошибки при выполнении
    // запроса и завершает выполнение в случае
    // найденных ошибок
    static public function trust()
    {
        self::$response->trust();
    }

    // выполнение контроллера прерывается ошибкой
    // $message - текст ошибки
    // $subject - код ошибки
    static public function stop($message = "", $subject = '')
    {
        self::$response->stop($message, $subject);
    }

    // получаем параметр запроса к контроллеру по его названию
    static public function param($name)
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

    static public function params($ps = "")
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

    static public function filterSub($fs, &$data)
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

    static public function filter($fs, $ps)
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

    static public function filterAll($clean_functions)
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
    static public function result($name, $value)
    {
        self::$response->result($name, $value);
    }

    static public function isAvailable($resource, $controller)
    {
        if (App::env() === "cli") {
            return true;
        }
        
        $module = Route::owner($controller);

        return ModuleRegistry::isActive($module) && Access::isHandlerPermitted($resource);
    }

    static protected function runHandler($handler)
    {
        if (is_file($handler . ".php")) {
            include $handler . ".php";
        } else {
            self::notFound();
        }
    }

    static protected function runInternalHandler($handler)
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

    static public function run($resource, $method)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return;
        }
        
        self::$stack[$top]["__resource"] = $resource;

        $isExternal = $top == 0;
        
        list($controller, $action, $data) = Url::route($resource, $method);
        
        if (!self::isAvailable($resource, $controller)) {
            if ($isExternal && $resource != 'errors/access-denied') {
                return self::run('errors/access-denied', 'get');
            } else {
                return '';
            }
        }

        self::$stack[$top] = array_merge(self::$stack[$top], $data);
        $handler = Url::handler($controller, $action);

        if ($isExternal) {
            Middleware::processBefore($resource, $action, self::$stack[$top], $method);
            self::runHandler($handler);
            Middleware::processAfter($resource, $action, self::$stack[$top], $method);
        } else {
            if (!self::runInternalHandler($handler)) {
                return '';
            }
        }

        if (!empty(self::$stack[$top]['display'])) {
            $handler .= '.' . self::$stack[$top]['display'];
        }
        
        if ($isExternal && self::$response->code == '404 Not Found') {
            list($controller, $action, $data) = Url::route('errors/not-found', 'get');
            $handler = Url::handler($controller, $action);
            self::$response->result("error", self::$response->code);
        }
        
        
        if ($isExternal && self::$response->code == '403 Forbidden') {
            list($controller, $action, $data) = Url::route('errors/not-found', 'get');
            $handler = Url::handler($controller, $action);
            self::$response->result("error", self::$response->code);
        }

        $r = self::$response->fetch($handler, $isExternal);
        if ($isExternal)
        {
            Language::rewrite($r);
        }
        return $r;
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


