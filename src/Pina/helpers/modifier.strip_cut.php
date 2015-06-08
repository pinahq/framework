<?php

function smarty_modifier_strip_cut($value, $link = false)
{
	$cut = \Pina\Parse::betweenMarkers($value, '<div class="cut">', '</div>');

	$replace = '';
	if ($link)
	{
		$replace = '<a href="'.$link.'">...</a>';
	}

	return str_replace('<div class="cut">'.$cut.'</div>', $replace, $value);
}