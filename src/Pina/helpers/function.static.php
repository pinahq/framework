<?php


function smarty_function_static($params, &$view)
{
    if (empty($params['src'])) {
        return '';
    }
    $static = \Pina\Config::get('app', 'static');
    return rtrim($static, '/') . '/'. ltrim($params['src'], '/');
}
