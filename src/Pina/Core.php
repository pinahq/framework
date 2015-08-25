<?php

namespace Pina;

class Core
{
    
    public static function version()
    {
        return '2.0.0';
    }

    public static function resource($resource = '')
    {
        static $item = false;

        if (!empty($resource) && empty($item)) {
            $item = $resource;
        }

        return $item;
    }

    public static function getResource(&$data, $method = 'get')
    {
        if (empty($data[$method])) {
            return '';
        }
        
        $parsed = parse_url($data[$method]);
        if (empty($parsed['path'])) {
            return '';
        }

        return $parsed['path'];
    }

    public static function getRequestData()
    {
        $data = array();
        if ($_SERVER["REQUEST_METHOD"] == 'GET') {
            $data = $_GET;
        } elseif ($_SERVER["REQUEST_METHOD"] == 'POST') {
            $data['__raw'] = file_get_contents('php://input');
            $data = $_POST;
        } else {
            $putdata = file_get_contents('php://input');
            $data['__raw'] = $putdata;
            $exploded = explode('&', $putdata);

            foreach ($exploded as $pair) {
                $item = explode('=', $pair);
                if (count($item) == 2) {
                    $data[urldecode($item[0])] = urldecode($item[1]);
                }
            }
        }
        return $data;
    }

    public static function getRequestMethod()
    {
        if (!isset($_SERVER["REQUEST_METHOD"])) return false;

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            return 'get';
        }

        if ($_SERVER["REQUEST_METHOD"] == "PUT" || ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['put']))) {
            return 'put';
        }

        if ($_SERVER["REQUEST_METHOD"] == "DELETE"
            || ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['delete']))) {
            return 'delete';
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            return 'post';
        }

        return false;
    }
    
    private static function produceModuleClass($p, $postfix)
    {
        list($module, $name) = explode("::", $p);
        
        $classname = "\\Pina\\Modules\\".$module."\\".$name.$postfix;
        
        return new $classname;
    }
    
    public static function finder($p)
    {
        return self::produceModuleClass($p, 'Finder');
    }
    
    public static function table($p)
    {
        return self::produceModuleClass($p, 'Gateway');
    }
    
    public static function domain($p)
    {
        return self::produceModuleClass($p, 'Domain');
    }
    
    
    public static function contentLocation($pattern = false, $params = array()) {
        static $data = false;

        if (!empty($pattern)) {
            $data = Route::resource($pattern, $params);   
        }
        
        if (empty($data)) {
            return Core::resource();
        }

        return $data;
    }
}
