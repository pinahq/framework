<?php

function smarty_function_json_encode($array)
{
	if (empty($array["value"])) return '{}';
	return json_encode($array["value"]);

}