<?php

function smarty_function_extends($ps, &$view)
{
    if (empty($ps['layout'])) return;
    \Pina\Request::setLayout($ps['layout']);
}