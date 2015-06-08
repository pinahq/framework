<?php

use Pina\Config;

function smarty_modifier_format_date($date)
{
	if (empty($date) || $date == '0000-00-00 00:00:00' || $date == "0000-00-00")
	{
		return '-';
	}

	if (is_string($date))
	{
		$date = strtotime($date);
	}
	
	$f = Config::get("appearance", "date_format");
	if (empty($f)) $f = "d.m.Y";
	
	return date($f, $date);
}