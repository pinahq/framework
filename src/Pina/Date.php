<?php

namespace Pina;

class Date
{
    public static function format($date)
    {
        if (empty($date) || $date == '0000-00-00 00:00:00' || $date == "0000-00-00")
        {
            return '-';
        }

        if (is_string($date))
        {
            $date = strtotime($date);
        }

        //TODO: configure settings loading
        //$f = Config::get("appearance", "date_format");
        if (empty($f)) $f = "d.m.Y";

        return date($f, $date);
    }
}