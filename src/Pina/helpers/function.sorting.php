<?php

function smarty_function_sorting($params, &$view)
{
	$view->assign("base", $params);

	return $view->fetch("skin/sorting.tpl");
}