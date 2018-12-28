<?php


function smarty_function_scripts($params, &$view)
{
	return \Pina\App::container()->get(\Pina\ResourceManagerInterface::class)->fetch('js');
}
