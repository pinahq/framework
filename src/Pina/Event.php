<?php

namespace Pina;

class Event extends Job
{

    private static $handlers = [];
    const EVENT_PREFIX = 'event:';

    public static function subscribe($event, $handler)
    {
        if (!isset(self::$handlers[$event]) || !is_array(self::$handlers[$event])) {
            self::$handlers[$event] = [];
            self::register($event);
        }
        
        if (in_array($handler, self::$handlers[$event])) {
            return;
        }

        self::$handlers[$event][] = $handler;
        Job::register($handler);
    }

    public static function trigger($event, $workload)
    {
        echo "trigger: ".self::EVENT_PREFIX.$event.":". $workload."\n";
        self::getClient()->doHighBackground(self::EVENT_PREFIX.$event, $workload);
    }

    public static function register($event)
    {
        self::getWorker()->addFunction(self::EVENT_PREFIX.$event, ['Pina\Event', 'handler']);
    }

    public static function handler($job)
    {
        echo "EVENT::HANDLER\n";
        print_r($job);
        
        $cmd = $job->functionName();
        if (strncmp($cmd, self::EVENT_PREFIX, strlen(self::EVENT_PREFIX)) !== 0) {
            return;
        }

        $cmd = substr($cmd, strlen(self::EVENT_PREFIX));
        $handlers = Event::getHandlers($cmd);
        if (empty($handlers) || !is_array($handlers)) {
            return;
        }

        $workload = $job->workload();
        foreach ($handlers as $handler) {
            Job::queueHigh($handler, $workload);
        }
    }

    public static function getHandlers($event)
    {
        if (empty(self::$handlers[$event])) {
           return [];
        }

        return self::$handlers[$event];
    }

}