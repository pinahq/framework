<?php

use Pina\Config;

function smarty_modifier_format_datetime($date)
{
	if (empty($date) || $date == '0000-00-00 00:00:00')
	{
		return '-';
	}

	if (is_string($date))
	{
		$date = strtotime($date);
	}
	
	$df = Config::get("appearance", "date_format");
	if (empty($df)) $df = "d.m.Y";
	
	$tf = Config::get("appearance", "time_format");
	if (empty($tf)) $tf = "H:i";

	return date($df." ".$tf, $date);
}