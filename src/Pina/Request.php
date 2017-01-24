<?php

namespace Pina;

class Request
{

    protected static $stack = [];
    protected static $messages = [];
    protected static $results = [];
    protected static $layout = 'main';
    protected static $lockLayout = false;
    protected static $done = false;

    public static function init($data)
    {
        self::$stack = array();

        if (is_array($data)) {
            array_push(self::$stack, $data);
        } else {
            array_push(self::$stack, array('__raw' => $data));
        }
    }

    public static function internal($resource, $method, $data = array())
    {
        ob_start();

        array_push(self::$stack, $data);
        self::run($resource, $method);
        array_pop(self::$stack);
        
        return ob_get_clean();
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
        return isset(self::$stack[$top]['__module'])?self::$stack[$top]['__module']:'';
    }

    public static function run($resource, $method)
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return '';
        }
        
        self::$done = false;
        self::$results = [];
        self::$messages = [];
        self::$stack[$top]["__resource"] = $resource;
        self::$stack[$top]["__method"] = $method;

        list($controller, $action, $data) = Url::route($resource, $method);
        self::$stack[$top] = array_merge(self::$stack[$top], $data);

        $display = isset(self::$stack[$top]['display'])?self::$stack[$top]['display']:'';
        
        $module = Route::owner($controller);
        self::$stack[$top]["__module"] = $module;
        if (empty($module)) {
            return self::notFound();
        }
        
        if (!self::isAvailable($module, $resource)) {
            return self::forbidden();
        }

        if (!self::runHandler(ModuleRegistry::getPath($module) . '/' . Url::handler($controller, $action))) {
            return false;
        }
        
        if (self::$done) {
            return false;
        }
        
        $response = Response\Factory::get($resource, $method);
        if (!empty(self::$messages)) {
            self::$results['__messages'] = self::$messages;
        }

        if ($top === 0) {
            self::contentType($response->contentType());
        }

        echo $response->fetch(self::$results, $controller, $action, $display, $top === 0);
        
        return true;
        
    }
    
    protected static function runHandler($handler)
    {
        if (is_file($handler . ".php")) {
            return include $handler . ".php";
        }
        
        return self::notFound();
    }

    public static function warning($message, $subject = '')
    {
        self::$messages[] = ['warning', $message, $subject];
    }

    public static function error($message, $subject = '')
    {
        self::$messages[] = ['error', $message, $subject];
    }

    public static function hasError()
    {
        foreach (self::$messages as $m) {
            if ($m[0] === 'error') {
                return true;
            }
        }
    }

    public static function result($name, $value)
    {
        // закидываем результаты выполнения запроса
        // во временный буфер,
        // чтобы потом отдать через json или xml
        self::$results[$name] = $value;
    }

    public static function header($h)
    {
        header($h);
    }

    public static function code($code)
    {
        static $lock = false;
        if (!empty($lock)) return;

        $lock = $code;
        self::header('HTTP/1.1 '.$code);
    }

    /* HTTP Codes 2xx */

    public static function ok()
    {
        self::code('200 OK');
    }

    public static function created($url)
    {
        self::code('201 Created');
        self::location($url);
        return Request::done();
    }

    public static function accepted($url)
    {
        self::code('202 Accepted');
        self::contentLocation($url);
        return Request::done();
    }

    public static function noContent()
    {
        self::code('204 No Content');
        return Request::done();
    }

    public static function partialContent($range)
    {
        self::code('206 Partial Content');
        self::contentRange($range);
        return Request::done();
    }

    /* HTTP Codes 3xx */

    public static function movedPermanently($url)
    {
        self::code('301 Moved Permanently');
        self::location($url);
        return Request::done();
    }

    public static function found($url)
    {
        self::code('302 Found');
        self::location($url);
        return Request::done();
    }

    public static function notModified()
    {
        self::code('304 Not Modified');
        return Request::done();
    }

    /* HTTP Codes 4xx */

    public static function badRequest($message = '', $subject = '')
    {
        if ($message) {
            self::$messages[] = ['error', $message, $subject];
        }
        return self::stopWithCode('400 Bad Request');
    }

    public static function unauthorized()
    {
        return self::stopWithCode('401 Unauthorized');
    }

    public static function forbidden()
    {
        return self::stopWithCode('403 Forbidden');
    }

    public static function notFound()
    {
        return self::stopWithCode('404 Not Found');
    }

    public static function requestTimeout()
    {
        return self::stopWithCode('408 Request Timeout');
    }

    public static function conflict()
    {
        return self::stopWithCode('409 Conflict');
    }

    public static function gone()
    {
        return self::stopWithCode('410 Gone');
    }

    public static function internalError($message = '', $subject = '')
    {
        if ($message) {
            self::$messages[] = ['error', $message, $subject];
        }
        return self::failWithCode('500 Internal Server Error');
    }

    public static function notImplemented()
    {
        return self::failWithCode('501 Not Implemented');
    }

    public static function badGateway()
    {
        return self::failWithCode('502 Bad Gateway');
    }

    public static function serviceUnavailable()
    {
        return self::failWithCode('503 Service Unavailable');
    }

    public static function gatewayTimeout()
    {
        return self::failWithCode('504 Gateway Timeout');
    }
    
    private static function stopWithCode($code)
    {
        $top = count(self::$stack) - 1;
        if ($top !== 0) {
            return self::done();
        }
        self::code($code);
        $number = strstr($code, ' ', true);
        if ($number) {            
            $response = Response\Factory::get('errors/' . $number, self::$stack[$top]['__method']);
            $results = ['error' => $code, '__messages' => self::$messages];
            self::contentType($response->contentType());
            echo $response->fetch($results, 'errors', 'show', '', true);
        }
        
        return self::done();
    }
    
    private static function failWithCode($code)
    {
        return self::stopWithCode($code);
    }
    
    public static function done()
    {
        self::$done = true;
        return false;
    }

    /* headers */

    public static function location($url)
    {
        self::header('Location: ' . $url);
    }

    public static function contentLocation($url)
    {
        self::header('Content-Location: ' . $url);
    }

    public static function contentType($type, $charset = false)
    {
        if (empty($charset)) {
            $charset = App::charset();
        }
        self::header('Content-Type: ' . $type . '; charset=' . $charset);
    }

    public static function contentRange($start, $end, $max)
    {
        self::header('Content-Range: bytes '.$start.'-'.$end.'/'.$max);
    }
    
    
    public static function setLayout($layout)
    {
        if (self::$lockLayout) {
            return;
        }
        self::$layout = $layout;
    }
    
    public static function getLayout()
    {
        return self::$layout;
    }
    
    public static function lockLayout()
    {
        self::$lockLayout = true;
    }

}
