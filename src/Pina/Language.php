<?php

namespace Pina;

class Language
{

    private static $code = 'en';
    private static $config = array();
    
    public static function init()
    {
        self::$config = Config::load('language');
        if (self::$config['default']) {
            self::$code = self::$config['default'];
        }
    }

    public static function code($code = '')
    {
        if ($code == '') {
            return self::$code;
        }

        $old_code = self::$code;

        self::$code = $code;

        return $old_code;
    }

    public static function getLanguage()
    {
        static $language = '';
        if (!empty($language)) {
            return $language;
        }

        $language = new Language;
        return $language;
    }

    public static function rewrite(&$content)
    {
        preg_match_all('/#\$\!([^#^\$^\!]*)\!\$#/iUS', $content, $matches);
        if (empty($matches[0])) {
            return $content;
        }
        
        $from = array();
        $to = array();
        if (!empty(self::$config['table'])) {        
            $table = new self::$config['table'];

            $ss = $table
                ->whereBy("string_key", $matches[1])
                ->whereBy('language_code', self::code())
                ->select('string_key, string_value')
                ->get();

            $from = array();
            $to = array();
            foreach ($ss as $s) {
                $from [] = "#$!" . $s["string_key"] . "!$#";
                $to [] = $s["string_value"];
            }
        } elseif (!empty(self::$config[self::code()])) {
            foreach ($matches[1] as $m) {
                if (!empty(self::$config[self::code()][$m])) {
                    $from[] = "#$!" . $m . "!$#";
                    $to[] = self::$config[self::code()][$m];
                }
            }
        }

        $content = str_replace($from, $to, $content);
        //return $content;
    }

    public static function key($key)
    {
        return "#$!" . $key . "!$#";
    }

    public static function val($key)
    {
        if (empty(self::$config['table'])) {
            return;
        }
        
        $table = new self::$config['table'];
        $value = $table
            ->whereBy('string_key', $key)
            ->whereBy('language_code', self::code())
            ->value('string_value');

        if (empty($value)) {
            return self::key($key);
        }

        return $value;
    }

}
