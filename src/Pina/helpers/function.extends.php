<?php

function smarty_function_extends($ps, &$view)
{
    if (empty($ps['layout'])) return;
    $view->setLayout($ps['layout']);
}