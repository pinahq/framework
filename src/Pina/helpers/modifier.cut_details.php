<?php

function smarty_modifier_cut_details($string)
{	
    $charset = \Pina\App::charset();
    if (empty($charset)) {
        $charset = 'utf-8';
    }
	
	if (($r = mb_strstr($string, '<hr class="pinacut" />', false, $charset)) !== false)
	{
		return $r;
	}
	
	return $string;
}