<?php

function smarty_function_place($params, &$view)
{
	if (empty($params['name'])) return '';
	return \Pina\Place::get($params['name']);
}