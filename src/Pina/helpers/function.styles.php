<?php


function smarty_function_styles($params, &$view)
{
	return \Pina\ResourceManager::fetch('css');
}
