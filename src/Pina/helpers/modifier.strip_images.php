<?php

function smarty_modifier_strip_images($value)
{
	$value = preg_replace("/<table.*?<\/table>/si", "", $value);
	$value = str_replace("<p>&nbsp;</p>", "", $value);
	return strip_tags($value, "<p><br><a><strong><ul><li><ol><h3><h2><h1>");
}