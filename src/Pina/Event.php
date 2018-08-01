<?php

namespace Pina;

class Event extends Request
{

    private static $syncHandlers = [];
    private static $asyncHandlers = [];
    
    public static function data()
    {
        return self::top()->data(); 
    }
    
    public static function subscribe($module, $event, $script = '')
    {
        if (!empty(App::container()->has(EventQueueInterface::class))) {
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

    public static function trigger($event, $data = '')
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

        if (isset(self::$asyncHandlers[$event]) && is_array(self::$asyncHandlers[$event]) && App::container()->has(EventQueueInterface::class)) {
            
            $queue = App::container()->get(EventQueueInterface::class);
            foreach (self::$asyncHandlers[$event] as $handler) {
                $queue->push($handler, $data);
            }
        }

    }

    public static function getAsyncHandlers()
    {
        return self::$asyncHandlers;
    }

}
