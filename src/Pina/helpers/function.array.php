<?php

function smarty_function_array($params, &$view)
{
	if (!empty($params["assign"]))
	{
		$values = $params;
		unset($values["assign"]);

		$view->assign($params["assign"], $values);
	}

	return "";
}