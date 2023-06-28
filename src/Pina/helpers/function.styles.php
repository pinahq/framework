<?php


use Pina\App;

function smarty_function_styles($params, &$view)
{
	return App::assets()->fetch('css');
}
