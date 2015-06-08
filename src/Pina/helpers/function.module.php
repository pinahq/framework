<?php

function smarty_function_module($params, &$view)
{
    return \Pina\Templater::processModule($params, $view);
}
