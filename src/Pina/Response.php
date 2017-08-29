<?php

namespace Pina;

use Pina\App;

class Response implements ResponseInterface
{

    protected $code = null;
    protected $location = null;
    protected $headers = [];
    protected $errors = [];
    protected $content = null;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public static function code($code)
    {
        return new Response($code);
    }

    public static function ok()
    {
        return static::code('200 OK');
    }

    public static function created($url)
    {
        return static::code('201 Created')->header('Location', $url);
    }

    public static function accepted($url)
    {
        return static::code('202 Accepted')->header('Content-Location', $url);
    }

    public static function noContent()
    {
        return static::code('204 No Content');
    }

    public static function partialContent($start, $end, $max)
    {
        return static::code('206 Partial Content')
                ->header('Content-Range: bytes ' . intval($start) . '-' . intval($end) . '/' . intval($max));
    }

    /* HTTP Codes 3xx */

    public static function movedPermanently($url)
    {
        return static::code('301 Moved Permanently')->header('Location', $url);
    }

    public static function found($url)
    {
        return static::code('302 Found')->header('Location', $url);
    }

    public static function notModified()
    {
        return static::code('304 Not Modified');
    }

    /* HTTP Codes 4xx */

    public static function badRequest($message = '', $subject = '')
    {
        return static::code('400 Bad Request')->error($message, $subject);
    }

    public static function unauthorized()
    {
        return static::stopWithCode('401 Unauthorized');
    }

    public static function forbidden()
    {
        return static::stopWithCode('403 Forbidden');
    }

    public static function notFound()
    {
        return static::stopWithCode('404 Not Found');
    }

    public static function requestTimeout()
    {
        return static::stopWithCode('408 Request Timeout');
    }

    public static function conflict()
    {
        return static::stopWithCode('409 Conflict');
    }

    public static function gone()
    {
        return static::stopWithCode('410 Gone');
    }

    public static function internalError($message = '', $subject = '')
    {
        return static::failWithCode('500 Internal Server Error')->error($message, $subject);
    }

    public static function notImplemented()
    {
        return static::failWithCode('501 Not Implemented');
    }

    public static function badGateway()
    {
        return static::failWithCode('502 Bad Gateway');
    }

    public static function serviceUnavailable()
    {
        return static::failWithCode('503 Service Unavailable');
    }

    public static function gatewayTimeout()
    {
        return static::failWithCode('504 Gateway Timeout');
    }

    private static function stopWithCode($code)
    {
        if (Request::isInternalRequest()) {
            return static::code($code);
        }
        $response = self::code($code);
        $number = strstr($code, ' ', true);
        if ($number) {
            $content = \Pina\App::createResponseContent(['error' => $code, 'id' => $code], 'errors', 'show');
            $response->setContent($content);
            return $response;
        }

        return $response;
    }

    private static function failWithCode($code)
    {
        return self::stopWithCode($code);
    }

    /* headers */

    public function location($url)
    {
        return $this->header('Location', $url);
    }

    public function contentLocation($url)
    {
        return $this->header('Content-Location', $url);
    }

    public function contentType($type, $charset = false)
    {
        if (empty($charset)) {
            $charset = App::charset();
        }
        return $this->header('Content-Type', $type . '; charset=' . $charset);
    }

    public static function contentRange($start, $end, $max)
    {
        return $this->header('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $max);
    }

    public function header($name, $value)
    {
        $this->headers[] = [$name, $value];
        return $this;
    }

    public function error($message, $subject = '')
    {
        if (empty($message)) {
            return $this;
        }
        $this->errors[] = [$message, $subject];
        return $this;
    }
    
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }
    
    public function hasContent()
    {
        return $this->content !== null;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    public function json($data)
    {
        return $this->setContent(new JsonContent($data));
    }

    public function send()
    {
        if (!empty($this->code)) {
            header('HTTP/1.1 ' . $this->code);
        }

        foreach ($this->headers as $header) {
            header($header[0] . ':' . $header[1]);
        }
        
        if (!empty($this->content)) {
            header('Content-Type: ' . $this->content->getType());
            $this->content->setErrors($this->errors);
            echo $this->content->fetch();
        }
    }

    public function fetchContent()
    {
        if (empty($this->content)) {
            return '';
        }
        $this->content->setErrors($this->errors);
        return $this->content->fetch();
    }

}
