<?php

namespace Pina;

class Language
{

    private static $code = null;
    private static $translator = null;
    private static $data = [];
    /**
     * Available languages array
     * @var string[]
     */
    private static $availableLanguages = [];
    private static $fallbackLanguages = [];

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

        if (!empty($code) && !in_array($code, static::$availableLanguages)) {
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

    public static function translate($string)
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

        if (isset(static::$translator)) {
            return static::$translator->translate(static::$code, $string);
        }

        if (!isset(static::$data[static::$code])) {
            static::$data[static::$code] = [];
        }

        if (!isset(static::$data[static::$code][$string])) {
            $file = App::path() . '/../lang/' . static::$code . '.php';
            static::$data[static::$code] = file_exists($file) ? include($file) : [];
        }

        return static::$data[static::$code][$string] ?? $string;
    }

    public static function setTranslator(TranslatorInterface $translator)
    {
        static::$translator = $translator;
    }

    /**
     * Пары ключ-значение, какие языки являются альтернативой
     * @param array $fallbackLanguages
     */
    public static function setFallbackLanguages(array $fallbackLanguages): void
    {
        self::$fallbackLanguages = $fallbackLanguages;
    }

    public static function getFallbackLanguage(string $language): string
    {
        return static::$fallbackLanguages[$language] ?? $language;
    }
}