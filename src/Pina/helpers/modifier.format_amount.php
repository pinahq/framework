<?php

function smarty_modifier_format_amount($items)
{
	$s = 'items';
	$i = intval($items);

	$i %= 100;
	if ($i > 20) $i %= 10;

	switch ($i)
	{
		case 1: $s = 'item'; break;
		case 2: case 3: case 4: $s = 'items'; break;
	}

	return $items. ' '.$s;
}
