<?php

function smarty_function_array_group($params, &$view) {
    if (empty($params['assign']) || !isset($params['from']) || !isset($params['column'])) {
        return '';
    }
    
    if (empty($params['from']) || !is_array($params['from'])) {
        $view->assign($params['assign'], []);
        return '';
    }

    $view->assign($params['assign'], \Pina\Arr::group($params['from'], $params['column']));
    
    return '';
}
