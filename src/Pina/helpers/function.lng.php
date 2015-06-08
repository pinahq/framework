<?php

function smarty_function_lng($params, &$view)
{
	if (!is_array($params) || !count($params) || empty($params['lng'])) return;

	if (count($params) == 1)
	{
		echo "#$!".$params["lng"]."!$#";
		return;
	}

	foreach ($params as $name => $value)
	{
		$view->assign($name, $value);
	}

	return $view->fetch('blocks/string/'.$params['lng'].'-'.Language::code().'.tpl');
}