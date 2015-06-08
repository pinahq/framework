<?php

function smarty_modifier_format_phone($phone)
{
	$tmp = explode(")", $phone);

	$c = count($tmp);
	$tail = trim($tmp[$c-1]);

	$tmp[$c-1] = str_replace($tail, "<span>".$tail."</span>", $tmp[$c-1]);

	return implode(")", $tmp);
}