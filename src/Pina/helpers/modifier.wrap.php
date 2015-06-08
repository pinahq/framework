<?php

function smarty_modifier_wrap($string, $class)
{
	return '<div class="'.$class.'">'.$string."</div>";
}