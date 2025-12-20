<?php

function smarty_function_link($params, &$view)
{
    if (empty($params['get'])) {
        $params['get'] = \Pina\Input::getResource();
    }

    $assign = '';
    if (isset($params['assign'])) {
        $assign = $params['assign'];
        unset($params['assign']);
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

    $link = \Pina\App::link($params['get'], $params, \Pina\Legacy\Request::resource());

    if ($assign) {
        $view->assign($assign, $link);
        $link = '';
    }

    return $link;
}
