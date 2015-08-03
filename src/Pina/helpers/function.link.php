<?php

function smarty_function_link($params, &$view)
{
    if (empty($params['get'])) {
        return '';
    }
        
	$link = \Pina\App::link($params['get'], $params);
    
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $link);
        $link = '';
    }
    
    return $link;
}
