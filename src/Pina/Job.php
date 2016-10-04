<?php

namespace Pina;

class Job
{
    const DIRECTORY = 'job';
    const JOB_PREFIX = 'job:';
    
    private static $client = null;
    private static $worker = null;
    private static $workload = '';
    
    public static function queue($cmd, $workload)
    {
        self::getClient()->doBackground(self::JOB_PREFIX.$cmd, $workload);
    }
    
    public static function queueHigh($cmd, $workload)
    {
        self::getClient()->doHighBackground(self::JOB_PREFIX.$cmd, $workload);
    }
    
    public static function queueLow($cmd, $workload)
    {
        self::getClient()->doLowBackground(self::JOB_PREFIX.$cmd, $workload);
    }
    
    public static function register($cmd)
    {
        self::getWorker()->addFunction(self::JOB_PREFIX.$cmd, ['Pina\Job', 'handler']);
    }
    
    public static function handler($job)
    {
        $cmd = $job->functionName();
        if (strncmp($cmd, self::JOB_PREFIX, strlen(self::JOB_PREFIX)) !== 0) {
            return;
        }
        $cmd = substr($cmd, strlen(self::JOB_PREFIX));
        $workload = $job->workload();
        self::run($cmd, $workload);
    }

    public static function run($cmd, $wordload)
    {
        $path = self::getPath($cmd);
        if (empty($path)) {
            Log::error("job", "Wrong command ".$cmd);
            return;
        }
        
        if (!file_exists($path)) {
            Log::error("job", "Command '".$cmd."' does not exist");
            return null;
        }

        self::$workload = $wordload;
        
        include $path;
    }
    
    public static function workload()
    {
        return self::$workload;
    }
    
    public static function work()
    {
        self::getWorker()->work();
        return self::getWorker()->returnCode() === GEARMAN_SUCCESS;
    }
    
    public static function getPath($cmd)
    {
        $parts = explode(".", $cmd);
        if (count($parts) !== 2) {
            return null;
        }

        list($group, $action) = $parts;

        $owner = Route::owner($group);
        $path = ModuleRegistry::getPath($owner);
        
        if (empty($path)) {
            return null;
        }
        
        return $path."/".self::DIRECTORY."/".$group."/".$action.".php";
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
