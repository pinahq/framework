<?php

function smarty_function_paging($params, &$view)
{
	$view->assign("base", $params);

	return $view->fetch("skin/paging.tpl");
}