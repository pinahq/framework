<?php

namespace Pina;

use Pina\Html\BaseHtml;
use Pina\Html\ZZ;

class Html extends BaseHtml
{

    public static function br()
    {
        return static::tag('br');
    }

    public static function li($content = '', $options = [])
    {
        return static::tag('li', $content, $options);
    }

    public static function p($content = '', $options = [])
    {
        return static::tag('p', $content, $options);
    }

    public static function nest($path, $content = '', $rootOptions = [])
    {
        $pathParts = explode('/', $path);
        while ($p = array_pop($pathParts)) {
            $siblings = explode('+', $p);
            $siblingContent = '';
            while ($s = array_shift($siblings)) {
                $options = [];
                $left = strlen($s);
                if (preg_match_all('/(([#.])([\w\-_ =]+))|(\[([#\w\-_ =.]+)\])/si', $s, $matches)) {
                    foreach ($matches[0] as $k => $full) {
                        $left = min($left, strpos($s, $full));
                        $prefix = !empty($matches[2][$k]) ? $matches[2][$k] : '[';
                        $value = $matches[3][$k];
                        $prop = '';
                        switch ($prefix) {
                            case '#':
                                $prop = 'id';
                                break;
                            case '.':
                                $prop = 'class';
                                break;
                            case '[':
                                $parts = explode('=', $matches[5][$k]);
                                $prop = $parts[0];
                                $value = isset($parts[1]) ? $parts[1] : $prop;
                                break;
                        }
                        if ($prop) {
                            $options[$prop] = (isset($options[$prop]) ? $options[$prop] . ' ' : '') . $value;
                        }
                    }
                    $s = substr($s, 0, $left);
                    if (empty($s)) {
                        $s = 'div';
                    }
                }

                if (empty($pathParts) && empty($siblings)) {
                    if (!empty($options['class']) && !empty($rootOptions['class'])) {
                        $rootOptions['class'] = $options['class'] . ' ' . $rootOptions['class'];
                    }
                    $options = array_merge($options, $rootOptions);
                }

                if (empty($siblings)) {
                    $content = $siblingContent . ($s == '%' ? $content : Html::tag($s, $content, $options));
                } else {
                    $siblingContent .= Html::tag($s, '', $options);
                }
            }
        }
        return $content;
    }

    /**
     * @param string $template
     * @param string $item
     * @return string
     * @throws \Exception
     */
    public static function zz(string $template, ?string $item = ''): string
    {
        $zz = new ZZ($template);
        $args = func_get_args();
        array_shift($args);
        return $zz->run($args);
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
