<?php

use Pina\Legacy\Route;
use Pina\Url;

function smarty_block_iflocation($params, $content, &$view, &$repeat) 
{
    if (empty($content) || empty($params['get'])) {
        return '';
    }

    $pattern = Url::trim($params['get']);
    $needed = Route::resource($pattern, $params);

    if (empty($needed)) {
        return '';
    }

    $resource = Url::trim(\Pina\App::resource());

    if (strpos($resource, $needed) !== 0)
        return '';
    list($preg, $map) = Url::preg($params['get']);

    $data = \Pina\Input::getData();

    unset($params['get']);
    $params = array_diff_key($params, array_flip($map));

    unset($data['get']);
    if (array_diff_assoc($data, $params) || array_diff_assoc($params, $data)) {
        return '';
    }

    return $content;
}
