<?php

namespace Pina;

class ResourceManager
{

    static $data = array('layout' => array(), 'content' => array());
    static $mode = 'content';

    static function append($type, $s)
    {
        if (empty(self::$data[self::$mode][$type]) 
            || !is_array(self::$data[self::$mode][$type])
        ) {
            self::$data[self::$mode][$type] = array();
        }
        self::$data[self::$mode][$type][] = $s;
    }

    static function fetch($type)
    {
        $data = array();
        foreach (self::$data as $values)
        {
            $data = Arr::merge($data, $values[$type]);
        }
        return implode("\r\n", array_unique($data));
    }

    static function mode($mode = '')
    {
        if ($mode) self::$mode = $mode;
        
        return self::$mode;
    }

}
