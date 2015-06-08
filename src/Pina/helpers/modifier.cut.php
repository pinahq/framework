<?php

function smarty_modifier_cut($string, $length = false, $etc = "...")
{	
    $charset = \Pina\App::charset();
    if (empty($charset)) {
        $charset = 'utf-8';
    }
	
	if (($pos = mb_strpos($string, '<hr class="pinacut" />')) !== false)
	{
		return mb_substr($string, 0, $pos, $charset);
	}

	if ($length && mb_strlen($string, $charset) > $length)
	{
		$string = strip_tags($string);
		$length -= min($length, mb_strlen($etc, $charset));
		return mb_substr($string, 0, $length, $charset).$etc;
	}
	
	return $string;
}