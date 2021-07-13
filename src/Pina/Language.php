<?php

namespace Pina;

class Language
{

    private static $code = null;
    private static $data = [];
    /**
     * Available languages array
     * @var string[]
     */
    private static $availableLanguages = [];

    /**
     * @param string[] $availableLanguages
     */
    public static function setAvailableLanguages(array $availableLanguages): void
    {
        static::$availableLanguages = $availableLanguages;
    }

    public static function init()
    {
        $config = Config::load('language');
        static::$code = $config['default'] ?? false;
    }

    /**
     * Returns enabled languages
     * @return string[]
     */
    public static function getAvailableLanguages(): array
    {
        return static::$availableLanguages;
    }

    /**
     * Returns other languages, different from given one
     * @param string $lang
     * @return array
     */
    public static function getOppositeLanguages(string $lang): array
    {
        return array_diff(static::getAvailableLanguages(), [$lang]);
    }

    public static function code($code = '')
    {
        if ($code === '') {
            return static::$code;
        }

        $oldCode = static::$code;
        static::$code = $code;

        return $oldCode;
    }

    public static function getCode()
    {
        return static::$code;
    }

    public static function translate($string, $ns = null)
    {
        if (!isset(static::$code)) {
            static::init();
        }

        if (empty(static::$code)) {
            return '';
        }

        $string = trim($string);
        if (empty($string)) {
            return '';
        }

        $module = $ns ? App::modules()->get($ns . "\\Module") : Request::module();
        if (empty($module)) {
            return $string;
        }

        $moduleKey = $module->getNamespace();

        if (!isset(static::$data[static::$code])) {
            static::$data[static::$code] = [];
        }

        if (!isset(static::$data[static::$code][$moduleKey])) {
            $path = $module->getPath();
            $file = $path . "/lang/" . static::$code . '.php';
            static::$data[static::$code][$moduleKey] = file_exists($file) ? include($file) : [];
        }

        if (!isset(static::$data[static::$code][$moduleKey][$string])) {
            $moduleKey = '__fallback__';
            $file = App::path() . '/../lang/' . static::$code . '.php';
            static::$data[static::$code][$moduleKey] = file_exists($file) ? include($file) : [];
        }

        return static::$data[static::$code][$moduleKey][$string] ?? $string;
    }
}
