<?php

namespace Pina;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Log
{

    private static $config = null;
    private static $loggers = [];

    public static function config()
    {
        if (!empty(self::$config)) {
            return self::$config;
        }
        self::$config = Config::load('log');
        return self::$config;
    }

    public static function get($name)
    {
        if (isset(self::$loggers[$name])) {
            return self::$loggers[$name];
        }
        
        $config = self::config();
        if (!is_callable($config)) {
            return;
        }
        
        $logger = new Logger($name);
        $config($logger);
        
        self::$loggers[$name] = $logger;
        return self::$loggers[$name];
    }

    public static function debug($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->debug($message, $context);
    }
    
    public static function info($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->info($message, $context);
    }
    
    public static function notice($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->notice($message, $context);
    }
    
    public static function warning($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->warning($message, $context);
    }

    public static function error($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->error($message, $context);
    }

    public static function critical($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->critical($message, $context);
    }

    public static function emergency($name, $message, $context = [])
    {
        $logger = self::get($name);
        $logger->emergency($message, $context);
    }

}