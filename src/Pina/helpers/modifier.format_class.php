<?php

function smarty_modifier_format_class($class)
{
	if (empty($class)) return '';

	$class = trim(str_replace(array("][", "]","["), "-", $class),"-");

	return $class;
}
