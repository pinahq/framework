<?php

namespace Pina;

class Validate
{

    public static function notEmpty($field, $message)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        if (Request::param($field) == '') {
            Request::error($message, $field);
        }
    }

    public static function inArray($field, $values, $message)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        $value = Request::param($field);
        if (!in_array($value, $values)) {
            Request::error($message, $field);
        }
    }

    public static function length($field, $length, $message)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        $value = Request::param($field);
        if (mb_strlen($value) > $length) {
            Request::error($message, $field);
        }
    }
    
    public static function email($field, $message)
    {
        if (!filter_var(Request::param($field), FILTER_VALIDATE_EMAIL)) {
            Request::error($message, $field);
        }
    }

}
