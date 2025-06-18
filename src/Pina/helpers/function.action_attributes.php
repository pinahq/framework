<?php

use Pina\Route;
use Pina\Url;
use Pina\App;
use Pina\CSRF;

function smarty_function_action_attributes($params, &$view)
{
    $availableMethods = array('get', 'post', 'put', 'delete');
    $methodsParams = array_intersect($availableMethods, array_keys($params));
    $method = reset($methodsParams);

    if (empty($method)) {
        return;
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

    $assign = '';
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }

    $pattern = $params[$method];
    unset($params[$method]);

    $resource = Route::resource($pattern, $params);

    $result = ' data-method="' . $method . '"';
    $result .= ' data-resource="' . ltrim($resource, '/') . '"';
    list($preg, $map) = Url::preg($pattern);
    $result .= ' data-params="' . htmlspecialchars(http_build_query(array_diff_key($params, array_flip($map))), ENT_COMPAT) . '"';

    $result .= ' ';

    if ($assign) {
        $view->assign($assign, $result);
        $link = '';
    }

    return $result;
}
