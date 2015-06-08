<?php

use Pina\Module;

function smarty_block_ifpermitted($params, $content, &$view, &$repeat)
{
	if (empty($content)) return "";

	$action = '';

    if (!isset($params['get'])) return '';

	if (!Module::isActive($params['get'])) return '';
	if (!Module::isPermitted($params['get'])) return '';

	return $content;
}