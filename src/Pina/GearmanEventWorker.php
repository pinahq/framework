<?php

namespace Pina;

class GearmanEventWorker
{
    private static $worker = null;
    
    public static function init()
    {
        $eventHandlers = Event::getAsyncHandlers();

        $summary = [];
        while ($handlers = array_pop($eventHandlers)) {
            $summary = array_merge($summary, $handlers);
        }
        $summary = array_unique($summary);
        if (empty($summary)) {
            return;
        }
        
        $worker = self::getWorker();
        foreach ($summary as $handler) {
            echo 'listen '.$handler."\n";
            $worker->addFunction($handler, ['Pina\GearmanEventWorker', 'handler']);
        }
        
    }
    
    public static function handler($job)
    {
        $cmd = $job->functionName();
        $data = $job->workload();
        
        list($namespace, $script) = explode('::', $cmd);
        
        $handler = new EventHandler($namespace, $script, $data);
        Event::push($handler);
        Event::run();
        Event::pop();
    }
    
    public static function work()
    {
        self::getWorker()->work();
        return self::getWorker()->returnCode() === GEARMAN_SUCCESS;
    }

    protected static function getWorker()
    {
        if(!is_null(self::$worker)) {
            return self::$worker;
        }
        
        $config = Config::load('gearman');
        self::$worker = new \GearmanWorker();
        self::$worker->addServer($config['host'], $config['port']);
        
        return self::$worker;
    }
    
}