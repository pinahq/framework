<?php

use Pina\Url;

function smarty_block_iflocation($params, $content, &$view, &$repeat)
{
	if (empty($content) || empty($params['get'])) return '';
	
	$pattern = Url::trim($params['get']);
    $needed = Url::resource($pattern, $params);

	if (empty($needed)) return '';
	
	$resource = Url::trim(\Pina\Core::resource());

	if (strpos($resource, $needed) === 0) return $content;

	return '';
}