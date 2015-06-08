<?php

use Pina\App;

function smarty_function_meta($params, &$view)
{
	return '<meta http-equiv="Content-Type" content="text/html; charset='.App::charset().'" />';
}