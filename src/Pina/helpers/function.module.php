<?php

function smarty_function_module($params, &$view)
{
    return \Pina\Legacy\Templater::processModule($params, $view);
}
