<?php

use Pina\Language;

function smarty_modifier_selector_items($str, $lng = true) {
	$items = explode(";", $str);
	foreach ($items as $k => $v)
	{
		$t = explode(":", $v);

		$items[$k] = array(
		    'value' => !empty($t[0])?$t[0]:'',
		    'caption' => !empty($t[1])?($lng?Language::key($t[1]):$t[1]):'',
		    'color' => !empty($t[2])?$t[2]:'orange'
		);
	}
	return $items;
}