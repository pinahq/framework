<?php

namespace Pina;

class Html extends BaseHtml
{

    public static function nest($path, $content)
    {
        $parts = array_reverse(explode('/', $path));
        foreach ($parts as $p) {
            $options = [];
            $left = strlen($p);
            if (preg_match_all('/([#.])([\w-_ ]+)/si', $p, $matches)) {
                foreach ($matches[0] as $k => $full) {
                    $left = min($left, strpos($p, $full));
                    $prefix = $matches[1][$k];
                    $value = $matches[2][$k];
                    switch ($prefix) {
                        case '#': $options['id'] = $value; break;
                        case '.': $options['class'] = $value; break;
                    }
                }
                $p = substr($p, 0, $left);
            }
            $content = Html::tag($p, $content, $options);
        }
        return $content;
    }

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

        $resource = Route::resource($pattern, $params);

        list($preg, $map) = Url::preg($pattern);
        $r = [
            'data-method' => $method,
            'data-resource' => ltrim($resource, '/'),
            'data-params' => http_build_query(array_diff_key($params, array_flip($map))),
        ];
        $r += CSRF::tagAttributeArray($method);

        return $r;
    }

}
