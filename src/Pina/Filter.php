<?php

namespace Pina;

class Filter
{

    static public function positive($v)
    {
        return $v < 0 ? 0 : $v;
    }

    static public function YN($v)
    {
        return $v != 'Y' ? 'N' : 'Y';
    }

    static public function dottedPrice($v)
    {
        return str_replace(',', '.', $v);
    }

    static public function http($v)
    {
        if (strpos(strtolower($v), 'http://') !== 0 && !empty($v)) {
            $v = 'http://' . $v;
        }
        return $v;
    }

}