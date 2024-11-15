<?php

namespace Pina;

class Input
{

    const ACTION_PARAM = 'action';
    const METHOD_DELIMITER = '!';

    private static $methods = array('get', 'post', 'put', 'delete');

    public static function getHost()
    {
        return !empty($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : '';
    }

    public static function getScheme()
    {
        return !empty($_SERVER["REQUEST_SCHEME"]) ? $_SERVER["REQUEST_SCHEME"] : 'http';
    }

    public static function getResource()
    {
        $r = self::getFullResource();

        if (empty($r)) {
            return '';
        }

        $parsed = parse_url($r);
        if (empty($parsed['path'])) {
            return '';
        }

        $path = $parsed['path'];

        foreach (self::$methods as $method) {
            if (strncasecmp($r, $method . self::METHOD_DELIMITER, strlen($method . self::METHOD_DELIMITER)) === 0) {
                return substr($path, strlen($method) + 1);
            }
        }

        return $path;
    }

    private static function getFullResource()
    {
        if (self::isScript()) {
            return isset($_REQUEST[self::ACTION_PARAM]) ? $_REQUEST[self::ACTION_PARAM] : '';
        }

        return ltrim(urldecode($_SERVER['REQUEST_URI']), '/');
    }

    public static function getMethod()
    {
        $realMethod = strtolower(isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET');
        if ($realMethod === 'get') {
            return $realMethod;
        }

        $r = self::getFullResource();

        $r = strtolower($r);

        foreach (self::$methods as $method) {
            if (strncmp($r, $method . self::METHOD_DELIMITER, strlen($method . self::METHOD_DELIMITER)) === 0) {
                return $method;
            }
        }

        return $realMethod;
    }

    public static function getData()
    {
        $data = array();
        if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == 'GET') {
            $data = $_GET;
        } elseif (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == 'POST') {
            $data = $_POST;
        } else {
            $contentType = self::getContentType();
            if ($contentType == 'application/x-www-form-urlencoded') {
                $input = file_get_contents('php://input');
                parse_str($input, $data);
            } elseif ($contentType == 'multipart/form-data') {
                $data = static::parseMultipartFormData();
            }
        }
        return $data;
    }

    protected static function parseMultipartFormData()
    {
        $r = [];

        $input = file_get_contents('php://input');
        $boundary = static::resolveBoundary();
        if (empty($boundary)) {
            parse_str(urldecode($input), $r);
            return $r;
        }

        $parts = preg_split("/-+$boundary/", $input);
        array_pop($parts);

        $str = '';
        foreach ($parts as $id => $block) {
            if (empty($block)) {
                continue;
            }

            if (strpos($block, 'application/octet-stream') !== FALSE) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $r['_FILES'][$matches[1]] = $matches[2];
            } else {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $str .= $matches[1]."=".$matches[2]."&";
            }
        }
        $parsed = [];
        parse_str($str, $parsed);
        return array_merge($r, $parsed);
    }

    protected static function resolveBoundary()
    {
        $matches = [];
        if (!preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches)) {
            return '';
        }
        return $matches[1];
    }

    public static function getContentType()
    {
        $contentTypeHeader = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : '';
        $contentType = $contentTypeHeader;
        if (strpos($contentTypeHeader, ';') !== false) {
            list($contentType, $tmp) = explode(';', $contentTypeHeader, 2);
        }
        return $contentType;
    }

    public static function isScript()
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }

        if (!empty($_SERVER['DOCUMENT_URI'])) {
            return strncmp($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'], strlen($_SERVER['DOCUMENT_URI'])) === 0;
        }

        return strncmp($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'], strlen($_SERVER['SCRIPT_NAME'])) === 0;
    }

}
