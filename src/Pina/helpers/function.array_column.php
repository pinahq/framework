<?php

function smarty_function_array_column($params, &$view) {
    if (empty($params['assign']) || !isset($params['from']) || !isset($params['column'])) {
        return '';
    }
    
    if (empty($params['from']) || !is_array($params['from'])) {
        $view->assign($params['assign'], []);
        return '';
    }

    $index = null;
    if (!empty($params['index'])) {
        $index = $params['index'];
    }
    
    $view->assign($params['assign'], array_column($params['from'], $params['column'], $params['index']));
    
    return '';
}
