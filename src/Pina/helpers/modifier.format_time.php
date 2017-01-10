<?php

use Pina\Config;

function smarty_modifier_format_time($date)
{
	if (is_string($date))
	{
		$date = strtotime($date);
	}
	
	#$f = Config::get("appearance", "time_format");
	if (empty($f)) $f = "H:i";

	return date($f, $date);
}