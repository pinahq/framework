<?php


function smarty_function_styles($params, &$view)
{
	return \Pina\App::container()->get(\Pina\ResourceManagerInterface::class)->fetch('css');
}
