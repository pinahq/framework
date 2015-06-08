<?php

function smarty_function_number_end_ru($params, &$view)
{
	$n = $params["value"];
	$t1 = $params["t1"];
	$t2 = $params["t2"];
	$t5 = !empty($params["t5"])?$params["t5"]:$params["t2"];
	$t = array($t1, $t2, $t5);
	$s = array (2, 0, 1, 1, 1, 2);
	return $t[($n%100>4&&$n%100<20)?2:$s[min($n%10,5)]];
}