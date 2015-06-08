<?php


function smarty_function_scripts($params, &$view)
{
	return \Pina\ResourceManager::fetch('js');
}
