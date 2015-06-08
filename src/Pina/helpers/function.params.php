<?php

function smarty_function_params($params, &$view)
{
	if (!empty($params["var"]))
	{
		$values = $params;
		unset($values["var"]);

		$view->assign($params["var"], $values);
	}

	return "";
}