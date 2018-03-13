<?php

use Pina\Url;
use Pina\Route;

function smarty_block_link_context($params, $content, &$view, &$repeat)
{
    global $__pinaLinkContext;
    
    if (!is_array($__pinaLinkContext)) {
        $__pinaLinkContext = [];
    }
    
    if ($repeat) {
        array_push($__pinaLinkContext, $params);
        return;
    }
    array_pop($__pinaLinkContext);

    return $content;
}