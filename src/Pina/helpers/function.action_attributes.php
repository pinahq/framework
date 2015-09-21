<?php

use Pina\Route;
use Pina\Url;
use Pina\App;

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
    
    $resource = Route::resource($pattern, $params);
    $prefix = App::getLinkPrefix($params);
    
    $result  = ' data-method="'.$method.'"';
    $result .= ' data-resource="'. $prefix . ltrim($resource, '/') . '"';
    list($preg, $map) = Url::preg($pattern);
    $result .= ' data-params="'.http_build_query(array_diff_key($params, array_flip($map))).'"';
    
    $result .= ' ';
    
    if ($assign) {
        $view->assign($assign, $result);
        $link = '';
    }
    
    return $result;
}
