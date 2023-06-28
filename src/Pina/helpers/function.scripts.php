<?php


use Pina\App;

function smarty_function_scripts($params, &$view)
{
	return App::assets()->fetch('js');
}
