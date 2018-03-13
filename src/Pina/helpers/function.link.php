<?php

function smarty_function_link($params, &$view)
{
    if (empty($params['get'])) {
        $params['get'] = \Pina\App::resource();
    }

    $assign = '';
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
    }

    global $__pinaLinkContext;

    if (is_array($__pinaLinkContext)) {
        $tmp = [];
        foreach ($__pinaLinkContext as $level) {
            foreach ($level as $k => $v) {
                if (isset($v) && $v !== '') {
                    $tmp[$k] = $v;
                }
            }
        }
        $params = array_merge($params, $tmp);
    }

    $link = \Pina\App::link($params['get'], $params);

    if ($assign) {
        $view->assign($assign, $link);
        $link = '';
    }

    return $link;
}
