<?php

function smarty_modifier_mine($a, $subject)
{
	return \Pina\Arr::mine($subject, $a);
}
