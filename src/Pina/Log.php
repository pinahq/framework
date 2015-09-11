<?php

namespace Pina;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

class Log
{

    private static $config = false;
    private static $loggers = array();

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
        $c = isset($config[$name])?$config[$name]:$config['default'];

        $logger = new Logger($name);
        foreach ($c as $level => $targets)
        {
            $l = false;
            switch ($level) {
                case 'debug': $l = Logger::DEBUG; break;
                case 'info': $l = Logger::INFO; break;
                case 'notice': $l = Logger::NOTICE; break;
                case 'warning': $l = Logger::WARNING; break;
                case 'error': $l = Logger::ERROR; break;
                case 'critical': $l = Logger::CRITICAL; break;
                case 'alert': $l = Logger::ALERT; break;
                case 'emergency': $l = Logger::EMERGENCY; break;
            }
            
            if (empty($l)) {
                continue;
            }

            foreach ($targets as $k => $t) {
                switch ($k) {
                    case 'file': $logger->pushHandler(new RotatingFileHandler($t[0], $t[1])); break;
                }
            }
        }
        self::$loggers[$name] = $logger;
        return self::$loggers[$name];
    }

    public static function warning($name, $message)
    {
        $logger = self::get($name);
        $logger->addWarning($message);
    }

    public static function error($name, $message)
    {
        $logger = self::get($name);
        $logger->addError($message);
    }

}