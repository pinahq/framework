<?php

function smarty_function_link($params, &$view)
{
    if (empty($params['get'])) {
        return '';
    }
        
	return \Pina\App::link($params['get'], $params);
}
