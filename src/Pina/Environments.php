<?php

namespace Pina;

class Environments
{

    static function checkPhpVersion()
    {
        $php_version_expected = '5.3';
        $php_version_value = phpversion();

        return array(
            'expected' => $php_version_expected,
            'value' => $php_version_value,
            'warning_type' => version_compare($php_version_value, $php_version_expected, '>=') ? 'ok' : 'error'
        );
    }

    static function checkPhpExtensions()
    {
        return array(
            'curl' => array('title' => 'CURL', 'info' => Language::key('env_test_ext_curl_info'), 'loaded' => extension_loaded('curl')),
            'gd' => array('title' => 'GD', 'info' => Language::key('env_test_ext_gd_info'), 'loaded' => extension_loaded('gd')),
            'mbstring' => array('title' => 'Multibyte String', 'info' => Language::key('env_test_ext_mbstring_info'), 'loaded' => extension_loaded('mbstring')),
            'mysql' => array('title' => 'MySQL', 'info' => Language::key('env_test_ext_mysql_info'), 'loaded' => extension_loaded('mysql')),
            'tokenizer' => array('title' => 'Tokenizer', 'info' => Language::key('env_test_ext_tokenizer_info'), 'loaded' => extension_loaded('tokenizer')),
            'iconv' => array('title' => 'Iconv', 'info' => Language::key('env_test_ext_iconv_info'), 'loaded' => function_exists('iconv')),
            'simplexml' => array('title' => 'SimpleXML', 'info' => Language::key('env_test_ext_simplexml_info'), 'loaded' => extension_loaded('simplexml')),
            'json' => array('title' => 'JSON', 'info' => Language::key('env_test_ext_json_info'), 'loaded' => extension_loaded('json')),
        );
    }

    static function checkPhpDirectives()
    {
        $safe_mode_expected = 'Off';
        $safe_mode_value = ini_get('safe_mode') ? 'On' : 'Off';

        $magic_quotes_runtime_expected = 'Off';
        $magic_quotes_runtime_value = ini_get('magic_quotes_runtime') ? 'On' : 'Off';

        $magic_quotes_sybase_expected = 'Off';
        $magic_quotes_sybase_value = ini_get('magic_quotes_sybase') ? 'On' : 'Off';

        $use_cookies_expected = 'On';
        $use_cookies_value = ini_get('session.use_cookies') ? 'On' : 'Off';

        $use_trans_sid_expected = 'On';
        $use_trans_sid_value = ini_get('session.use_trans_sid') ? 'On' : 'Off';

        $memory_limit_expected = '32M';
        $memory_limit_value = ini_get('memory_limit');

        return array(
            'safe_mode' => array(
                'expected' => '= ' . $safe_mode_expected,
                'value' => $safe_mode_value,
                'warning_type' => $safe_mode_expected == $safe_mode_value ? 'ok' : 'error'
            ),
            'magic_quotes_runtime' => array(
                'expected' => '= ' . $magic_quotes_runtime_expected,
                'value' => $magic_quotes_runtime_value,
                'warning_type' => $magic_quotes_runtime_expected == $magic_quotes_runtime_value ? 'ok' : 'error'
            ),
            'magic_quotes_sybase' => array(
                'expected' => '= ' . $magic_quotes_sybase_expected,
                'value' => $magic_quotes_sybase_value,
                'warning_type' => $magic_quotes_sybase_expected == $magic_quotes_sybase_value ? 'ok' : 'error'
            ),
            'memory_limit' => array(
                'expected' => '>= ' . $memory_limit_expected,
                'value' => $memory_limit_value,
                'warning_type' => self::return_bytes($memory_limit_value) >= self::return_bytes($memory_limit_expected) ? 'ok' : 'warning'
            ),
        );
    }

    static function checkRecommendedApacheModules()
    {
        $apache_modules_recommended = array();
        if (function_exists("apache_get_modules")) {
            $apache_modules = apache_get_modules();

            $apache_modules_recommended = array(
                'mod_deflate' => array('title' => 'Deflate', 'info' => Language::key('env_test_apache_deflate_info'), 'loaded' => in_array('mod_deflate', $apache_modules)),
                'mod_rewrite' => array('title' => 'ModRewrite', 'info' => Language::key('env_test_apache_rewrite_info'), 'loaded' => in_array('mod_rewrite', $apache_modules)),
            );
        }
        return $apache_modules_recommended;
    }

    static function checkPermissions($extra = array())
    {
        $permissions = array(
            "var/cache" => array("expected" => "writable"),
            //"var/error-reporter" => array("expected" => "writable"),
            "var/compiled" => array("expected" => "writable"),
            //"var/debug" => array("expected" => "writable"),
            "var/log" => array("expected" => "writable"),
            //"var/temp" => array("expected" => "writable"),
            //"images" => array("expected" => "writable"),
            "public/cache" => array("expected" => "writable"),
            "public/uploads/images" => array("expected" => "writable"),
            //"attachments" => array("expected" => "writable"),
        );
        $permissions = array_merge($extra, $permissions);

        foreach ($permissions as $file => $item) {
            if (empty($item["expected"])) {
                continue;
            }
            $func = "is_" . $item["expected"];
            $item["value"] = $func(App::path().'/../'.$file) ? $item["expected"] : ("not " . $item["expected"]);
            $item["warning_type"] = $item["value"] == $item["expected"] ? "ok" : "error";
            $permissions[$file] = $item;
        }
        return $permissions;
    }

    static function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

}