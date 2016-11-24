<?php

namespace Pina\Response;

use Pina\App;

class Response
{

    public $messages = array();
    public $isFailed = false;
    public $code = '';

    public function warning($message, $subject = '')
    {
        $this->messages[] = array('warning', $message, $subject);
    }

    public function error($message, $subject = '')
    {
        $this->messages[] = array('error', $message, $subject);
        $this->isFailed = true;
    }

    public function stop($message, $subject = '')
    {
        $this->messages[] = array('error', $message, $subject);
        $this->isFailed = true;
        $this->trust();
    }

    public function trust()
    {
        if ($this->isFailed) {
            $this->fail();
        }
    }

    public function result($name, $value)
    {
        // закидываем результаты выполнения запроса
        // во временный буфер,
        // чтобы потом отдать через json или xml
        $this->results[$name] = $value;
    }

    public function header($h)
    {
        @header($h);
    }
    
    public function code($code)
    {
        static $lock = false;
        if (!empty($lock)) return;
        
        $lock = $code;
        $this->code = $code;
        $this->header('HTTP/1.1 '.$code);
    }

    /* HTTP Codes 2xx */

    public function ok()
    {
        $this->code('200 OK');
    }

    public function created($url)
    {
        $this->code('201 Created');
        $this->location($url);
    }

    public function accepted($url)
    {
        $this->code('202 Accepted');
        $this->contentLocation($url);
    }

    public function noContent()
    {
        $this->code('204 No Content');
    }

    public function partialContent($range)
    {
        $this->code('206 Partial Content');
        $this->contentRange($range);
    }

    /* HTTP Codes 3xx */

    public function movedPermanently($url)
    {
        $this->code('301 Moved Permanently');
        $this->location($url);
    }

    public function found($url)
    {
        $this->code('302 Found');
        $this->location($url);
    }

    public function notModified()
    {
        $this->code('Not Modified');
    }

    /* HTTP Codes 4xx */

    public function badRequest()
    {
        $this->code('400 Bad Request');
    }

    public function unauthorized()
    {
        $this->code('401 Unauthorized');
    }

    public function forbidden()
    {
        $this->code('403 Forbidden');
    }

    public function notFound()
    {
        $this->code('404 Not Found');
    }

    public function requestTimeout()
    {
        $this->code('408 Request Timeout');
    }

    public function conflict()
    {
        $this->code('409 Conflict');
    }

    public function gone()
    {
        $this->code('410 Gone');
    }
    
    public function internalError()
    {
        $this->code('500 Internal Server Error');
    }
    
    public function notImplemented()
    {
        $this->code('501 Not Implemented');
    }
    
    public function badGateway()
    {
        $this->code('502 Bad Gateway');
    }
    
    public function serviceUnavailable()
    {
        $this->code('503 Service Unavailable');
    }
    
    public function gatewayTimeout()
    {
        $this->code('504 Gateway Timeout');
    }

    /* headers */

    public function location($url)
    {
        $this->header('Location: ' . $url);
    }

    public function contentLocation($url)
    {
        $this->header('Content-Location: ' . $url);
    }

    public function contentType($type, $charset = false)
    {
        if (empty($charset)) {
            $charset = App::charset();
        }
        $this->header('Content-type: ' . $type . '; charset=' . $charset);
    }

    public function contentRange($range)
    {
        
    }

}
