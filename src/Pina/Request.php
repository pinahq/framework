<?php

namespace Pina;

class Request
{

    protected static $stack = [];

    public static function internal($call)
    {
        ob_start();

        self::push($call);
        self::run();
        self::pop();
        
        return ob_get_clean();
    }
    
    public static function top()
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return null;
        }

        return self::$stack[$top];
    }
    
    public static function isInternalRequest()
    {
        return count(self::$stack) > 1;
    }
    
    public static function isExternalRequest()
    {
        return count(self::$stack) === 1;
    }

    public static function set($name, $value)
    {
        self::top()->set($name, $value);
    }

    public static function match($pattern)
    {
        $resource = Url::trim(self::top()->resource());

        if (empty($resource)) {
            return;
        }

        $pattern = Url::trim($pattern);
        $parsed = Url::parse($resource, $pattern);
        foreach ($parsed as $k => $v) {
            self::set($k, urldecode($v));
        }
    }

    // получаем параметр запроса к контроллеру по его названию
    public static function param($name)
    {
        return self::top()->param($name);
    }

    public static function params($ps = "")
    {
        return self::top()->params($ps);
    }
    
    public static function exists($key)
    {
        return self::top()->exists($key);
    }
    
    public static function has($key)
    {
        return self::top()->has($key);
    }
    
    public static function all()
    {
        return self::top()->all();
    }
    
    public static function input($name, $default)
    {
        return self::top()->input($name, $default);
    }
    
    public static function only($keys)
    {
        return self::top()->only($keys);
    }
    
    public static function except($keys)
    {
        return self::top()->except($keys);
    }

    public static function intersect($keys)
    {
        return self::top()->intersect($keys);
    }

    public static function raw()
    {
        return self::top()->raw();
    }

    public static function filter($fs, $ps)
    {
        self::top()->filter($fs, $ps);
    }

    public static function filterAll($clean_functions)
    {
        self::top()->filterAll($clean_functions);
    }
    
    public static function module()
    {
        return self::top()->module();
    }
    
    public static function push($call)
    {
        array_push(self::$stack, $call);
    }
    
    public static function pop()
    {
        array_pop(self::$stack);
    }

    public static function run()
    {
        return self::top()->run();
    }

    public static function warning($message, $subject = '')
    {
        self::top()->message('warning', $message, $subject);
    }

    public static function error($message, $subject = '')
    {
        self::top()->message('error', $message, $subject);
    }

    public static function hasError()
    {
        return self::top()->hasError();
    }

    public static function result($name, $value)
    {
        self::top()->result($name, $value);
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
        if (self::isInternalRequest()) {
            return self::done();
        }
        self::code($code);
        $number = strstr($code, ' ', true);
        if ($number) {            
            $response = Response\Factory::get('errors/' . $number, self::top()->method());
            $results = ['error' => $code, '__messages' => self::top()->messages()];
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
        self::top()->done();
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
        self::top()->setLayout($layout);
    }
    
    public static function getLayout()
    {
        return self::top()->getLayout();
    }

}
