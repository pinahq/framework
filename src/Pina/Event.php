<?php

namespace Pina;

class Event extends Request
{

    private static $config = null;
    private static $syncHandlers = [];
    private static $asyncHandlers = [];
    
    public static function data()
    {
        return self::top()->data($ps); 
    }
    
    public static function subscribe($module, $event, $script = '')
    {
        if (!self::$config) {
            self::$config = Config::load('events');
        }

        if (!empty(self::$config['queue'])) {
            self::subscribeAsync($module, $event, $script);
        } else {
            self::subscribeSync($module, $event, $script);
        }
    }

    public static function subscribeSync($module, $event, $script = '')
    {
        if (!isset(self::$syncHandlers[$event]) || !is_array(self::$syncHandlers[$event])) {
            self::$syncHandlers[$event] = [];
        }

        $handler = $module->getNamespace() . '::' . ($script ? $script : $event);

        if (in_array($handler, self::$syncHandlers[$event])) {
            return;
        }

        self::$syncHandlers[$event][] = $handler;
    }

    public static function subscribeAsync($module, $event, $script = '')
    {
        if (!isset(self::$asyncHandlers[$event]) || !is_array(self::$asyncHandlers[$event])) {
            self::$asyncHandlers[$event] = [];
        }

        $handler = $module->getNamespace() . '::' . ($script ? $script : $event);

        if (in_array($handler, self::$asyncHandlers[$event])) {
            return;
        }

        self::$asyncHandlers[$event][] = $handler;
    }

    public static function trigger($event, $data)
    {
        if (isset(self::$syncHandlers[$event]) && is_array(self::$syncHandlers[$event])) {

            foreach (self::$syncHandlers[$event] as $handler) {
                list($namespace, $script) = explode('::', $handler);
                $handler = new EventHandler($namespace, $script, $data);
                Event::push($handler);
                Event::run();
                Event::pop();
            }
        }

        if (isset(self::$asyncHandlers[$event]) && is_array(self::$asyncHandlers[$event]) && !empty(self::$config['queue'])) {
            
            $classname = self::$config['queue'];
            foreach (self::$asyncHandlers[$event] as $handler) {
                call_user_func_array([$classname, 'push'], [$handler, $data]);
            }
        }

    }
    
    public static function getAsyncHandlers()
    {
        return self::$asyncHandlers;
    }

    public static function register($event)
    {
        self::getWorker()->addFunction(self::EVENT_PREFIX . $event, ['Pina\Event', 'handler']);
    }

    public static function handler($job)
    {
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
