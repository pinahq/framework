<?php

namespace Pina;

class Event extends Request
{

    const PRIORITY_HIGH = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_LOW = 2;
    const MODE_SYNC = 0;
    const MODE_ASYNC = 1;

    public static function data()
    {
        return static::top()->data();
    }

    public static function subscribe($module, $event, $script = '', $priority = Event::PRIORITY_NORMAL)
    {
        $handler = new Events\ModuleEventHandler($module, $script ? $script : $event);
        App::events()->subscribe($event, $handler, $priority);
    }

    public static function subscribeSync($module, $event, $script = '', $priority = Event::PRIORITY_NORMAL)
    {
        $handler = new Events\ModuleEventHandler($module, $script ? $script : $event);
        App::events()->subscribeSync($event, $handler, $priority);
    }

    public static function trigger($event, $data = '')
    {
        //триггерим старые события для совместимости
        App::events()->trigger($event, $data);

        //триггерим новые события
        App::event($event)->trigger($data);
    }

}
