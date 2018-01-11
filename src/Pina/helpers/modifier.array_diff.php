<?php

function smarty_modifier_array_diff($array)
{
    if (!is_array($array)) {
        return $array;
    }
    $args = func_get_args();
    array_shift($args);
    foreach ($args as $arg) {
        $array = array_diff($array, is_array($arg) ? $arg : [$arg]);
    }

    return $array;
}
