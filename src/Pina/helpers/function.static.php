<?php

function smarty_function_static($params, &$view)
{
    if (empty($params['src'])) {
        return '';
    }
    $static = \Pina\Config::get('app', 'static');
    $version = \Pina\App::version();
    $v = $version ? ('?' . $version) : '';
    return rtrim($static, '/') . '/' . ltrim($params['src'], '/') . $v;
}
