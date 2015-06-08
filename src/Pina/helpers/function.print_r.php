<?php


function smarty_function_print_r($params, &$smarty)
{	
	return '<pre>'.print_r($params, 1).'</pre>';
}