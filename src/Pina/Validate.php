<?php

namespace Pina;

class Validate
{

    public static function token($field, $message = '')
    {
        $user_login = Request::param($field);
        for ($i = 0; $i < strlen($user_login); $i++) {
            if (!(
                ($user_login[$i] >= 'a' && $user_login[$i] <= 'z') ||
                ($user_login[$i] >= 'A' && $user_login[$i] <= 'Z') ||
                ($user_login[$i] >= '0' && $user_login[$i] <= '9') ||
                ($user_login[$i] == '-' || $user_login[$i] == '_')
                )
            ) {
                if (empty($message)) {
                    $message = Language::val('only_letters_numbers_dash_accepted');
                }
                Request::error($message, $field);
                break;
            }
        }
    }

    public static function notEmpty($field, $message)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        if (Request::param($field) == '') {
            Request::error($message, $field);
        }
    }

    public static function dateTime($field)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        $date = Request::param($field);
        if (!preg_match("/^\s*[\d]{2}\.[\d]{2}\.[\d]{4}\s+[\d]{2}\:[\d]{2}(\:[\d]{2})?\s*$/i", $date)) {
            Request::error(Language::val('wrong_date_format'), $field);
        }
    }

    public static function date($field)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        $date = Request::param($field);
        if (!preg_match("/^\s*[\d]{2}\.[\d]{2}\.[\d]{4}\s*$/i", $date)) {
            Request::error(Language::val('wrong_date_format'), $field);
        }
    }

    public static function dateStartEnd()
    {
        if (Request::param($field) === null) {
            return;
        }
        
        $start = Request::param('date_start');
        $end = Request::param('date_end');
        if ($start && $end) {
            if ($start >= $end) {
                Request::error(Language::val('date_start_end_explanation'), 'date_end');
            }
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
    
    public static function price($field)
    {
        if (Request::param($field) === null) {
            return;
        }
        
        if (strval(floatval(Request::param($field))) != Request::param($field) ||
            Request::param($field) >= 1000000000) {
            Request::error(lng("wrong_price_format"), $field);
        }
    }
}
