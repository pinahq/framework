<?php

namespace Pina\Response;

use Pina\App;

class Factory
{

    public static function get($resource, $method)
    {
        static $responses = [];
        $type = self::parseType($resource, $method);

        if (isset($responses[$type])) {
            return $responses[$type];
        }

        switch ($type) {
            case 'html': $responses[$type] = new HtmlResponse();
                break;
            case 'json': $responses[$type] = new JsonResponse();
                break;
            case 'redirect': $responses[$type] = new RedirectResponse();
                break;
            default: throw new \Exception('wrong response type');
        }

        return $responses[$type];
    }

    private static function parseType($resource, $method)
    {

        $info = pathinfo($resource);
        $extension = !empty($info['extension']) ? $info['extension'] : '';
        if (in_array($extension, array('', 'html')) && $method == "get") {
            return 'html';
        }
        
        if (in_array($extension, array('json')) ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            return 'json';
        }

        return 'redirect';
    }

}
