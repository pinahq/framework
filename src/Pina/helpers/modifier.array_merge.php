<?php

function smarty_modifier_array_merge($array)
{
    if (!is_array($array)) {
        $array = [];
    }
    
    $args = func_get_args();
    array_shift($args);
    foreach ($args as $arg) {
        $array = array_merge($array, is_array($arg) ? $arg : [$arg]);
    }

    return $array;
}
