<?php

namespace Pina;

class Event extends Request
{

    const PRIORITY_HIGH = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_LOW = 2;
    const MODE_SYNC = 0;
    const MODE_ASYNC = 1;

    public static function trigger($event, $data = '')
    {
        //триггерим новые события
        App::event($event)->trigger($data);
    }

}
