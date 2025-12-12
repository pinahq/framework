<?php

namespace Pina\Legacy;

use Pina\Arr;

class Composer
{

    private static $data = array();

    public static function place($mode, $pos, $get, $params = array())
    {
        if (!isset(self::$data[$pos]) || !is_array(self::$data[$pos])) {
            self::$data[$pos] = array();
        }
        $line = $params;
        $line['get'] = $get;
        $line['__widget'] = $mode;
        self::$data[$pos] [] = $line;
    }

    public static function placeModule($pos, $get, $params = array())
    {
        self::place('module', $pos, $get, $params);
    }

    public static function placeView($pos, $get, $params = array())
    {
        self::place('view', $pos, $get, $params);
    }

    public static function draw($pos, $ps, &$view)
    {
        if (!isset(self::$data[$pos])) {
            return '';
        }
        $items = self::$data[$pos];
        if (!is_array($items)) {
            return '';
        }

        $r = '';
        foreach ($items as $params) {
            $params = Arr::merge($ps, $params);
            if ($params['__widget'] == 'view') {
                $r .= Templater::processView($params, $view);
            } else {
                $r .= Templater::processModule($params, $view);
            }
        }
        return $r;
    }

}