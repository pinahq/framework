<?php

function smarty_modifier_htmlall($string)
{	
    $charset = \Pina\App::charset();
    if (empty($charset)) {
        $charset = 'utf-8';
    }
	
	return htmlentities($string, ENT_QUOTES, $charset);
}
