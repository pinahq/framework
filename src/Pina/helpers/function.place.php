<?php

function smarty_function_place($params, &$view) {
    if (empty($params['name'])) {
        return '';
    }

    $r = \Pina\Place::get($params['name']);

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $r);
        $r = '';
    }
    
    return $r;
}
