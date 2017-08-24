<?php

namespace Pina;

class GearmanEventWorker
{
    private $worker = null;
    
    public function __construct()
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
        
        $config = Config::load('gearman');
        $this->worker = new \GearmanWorker();
        $this->worker->addServer($config['host'], $config['port']);
        
        foreach ($summary as $handler) {
            echo 'listen '.$handler."\n";
            $this->worker->addFunction($handler, [$this, 'handler']);
        }
        
    }
    
    public function handler($job)
    {
        $cmd = $job->functionName();
        $data = $job->workload();
        
        list($namespace, $script) = explode('::', $cmd);
        
        $handler = new EventHandler($namespace, $script, $data);
        Event::push($handler);
        Event::run();
        Event::pop();
    }
    
    public function work()
    {
        $this->worker->work();
        return $this->worker->returnCode() === GEARMAN_SUCCESS;
    }

}