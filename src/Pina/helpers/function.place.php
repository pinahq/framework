<?php

function smarty_function_place($params, &$view) {
    if (empty($params['name'])) {
        return '';
    }

    $r = \Pina\Request::getPlace($params['name']);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $r);
        $r = '';
    }
    
    return $r;
}
