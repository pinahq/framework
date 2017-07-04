<?php

namespace Pina;

class GearmanEventQueue
{
    private static $client = null;
    
    public static function push($handler, $data)
    {
        self::getClient()->doBackground($handler, $data);
    }
    
    protected static function getClient()
    {
        if(!is_null(self::$client)) {
            return self::$client;
        }
        
        $config = Config::load('gearman');
        self::$client = new \GearmanClient();
        self::$client->addServer($config['host'], $config['port']);
        
        return self::$client;
    }
    
    
}