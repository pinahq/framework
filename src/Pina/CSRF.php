<?php

namespace Pina;

class CSRF
{
    
    private static $whitelist = [];
    private static $expired = 3600;
    private static $saveMethods = ['get', 'head', 'options'];
    private static $token = null;

    public static function whitelist($list)
    {
        foreach ($list as $k => $v) {
            $list[$k] = ltrim($v, '/');
        }
        self::$whitelist = array_unique(array_merge(self::$whitelist, $list));
        return $list;
    }
    
    public static function skipMethod($method)
    {
        self::$saveMethods[] = $method;
    }
    
    private static function generate()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $length = mt_rand(4, 32);

        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return uniqid($code . time(), true);
    }

    public static function init()
    {
        if (self::$token) {
            return;
        }
        self::$token = isset($_COOKIE['csrf_token']) ? $_COOKIE['csrf_token'] : '';
        if (!self::$token) {
            self::$token = self::generate();
        }
        
        $expired = Config::get('app', 'csrf_token_expired');
        if (is_null($expired)) {
             $expired = self::$expired;
        }
        
        setcookie('csrf_token', self::$token, time() + $expired, '/');
    }
    
    public static function formField($method)
    {
        if (in_array(strtolower($method), self::$saveMethods)) {
            return '';
        }
        return '<input type="hidden" name="csrf_token" value="'.self::token().'" />';
    }
    
    public static function tagAttribute($method)
    {
        if (in_array(strtolower($method), self::$saveMethods)) {
            return '';
        }
        return ' data-csrf-token="'.self::token().'"';
    }
    
    public static function token()
    {
        self::init();
        return self::$token;
    }

    public static function verify($controller, $data)
    {
        if (in_array(Input::getMethod(), self::$saveMethods)) {
            return true;
        }
        if (in_array(Route::base($controller), self::$whitelist)) {
            return true;
        }
        $cookie = isset($_COOKIE['csrf_token']) ? $_COOKIE['csrf_token'] : '';
        $header = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        if (!empty($header)) {
            return $header === $cookie;
        }
        
        if (empty($data['csrf_token'])) {
            return false;
        }
        
        return $data['csrf_token'] === $cookie;
    }
    
}
