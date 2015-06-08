<?php

function smarty_modifier_number_end_ru($n, $t1, $t2, $t5 = '') {
	$t = array($t1, $t2, $t5);
	$s = array (2, 0, 1, 1, 1, 2);
	return $n." ".$t[($n%100>4&&$n%100<20)?2:$s[min($n%10,5)]];
}