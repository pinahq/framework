<?php

use Pina\Url;
use Pina\Route;

function smarty_block_iflocation($params, $content, &$view, &$repeat)
{
	if (empty($content) || empty($params['get'])) return '';
	
	$pattern = Url::trim($params['get']);
    $needed = Route::resource($pattern, $params);

	if (empty($needed)) return '';
	
	$resource = Url::trim(\Pina\Core::resource());

	if (strpos($resource, $needed) === 0) return $content;

	return '';
}