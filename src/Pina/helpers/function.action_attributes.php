<?php

use Pina\Route;
use Pina\Url;

function smarty_function_action_attributes($params, &$view)
{
    $availableMethods = array('get', 'post', 'put', 'delete');
    $methodsParams = array_intersect($availableMethods, array_keys($params));
    $method = reset($methodsParams);
    
    if (empty($method)) {
        return;
    }
    
    $assign = '';
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }
    
    $pattern = $params[$method];
    unset($params[$method]);
    
    $result  = ' data-method="'.$method.'"';
    $result .= ' data-resource="'.Route::resource($pattern, $params).'"';
    list($preg, $map) = Url::preg($pattern);
    $result .= ' data-params="'.http_build_query(array_diff_key($params, array_flip($map))).'"';
    
    $result .= ' ';
    
    if ($assign) {
        $view->assign($assign, $result);
        $link = '';
    }
    
    return $result;
}
