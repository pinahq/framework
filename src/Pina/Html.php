<?php

namespace Pina;

class Html extends \SimpleHtml\Html
{

    public static function getActionAttributes($method, $pattern, $params)
    {
        $availableMethods = array('get', 'post', 'put', 'delete');

        if (!in_array($method, $availableMethods)) {
            return [];
        }

        global $__pinaLinkContext;

        if (is_array($__pinaLinkContext)) {
            foreach ($__pinaLinkContext as $level) {
                foreach ($level as $k => $v) {
                    if (isset($v) && $v !== '' && !isset($params[$k])) {
                        $params[$k] = $v;
                    }
                }
            }
        }

        $resource = Url::resource($pattern, $params);

        list($preg, $map) = Url::preg($pattern);
        $r = [
            'data-method' => $method,
            'data-resource' => ltrim($resource, '/'),
            'data-params' => http_build_query(array_diff_key($params, array_flip($map))),
        ];
        return $r;
    }

}
