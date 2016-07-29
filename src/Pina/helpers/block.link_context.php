<?php

use Pina\Url;
use Pina\Route;

function smarty_block_link_context($params, $content, &$view, &$repeat)
{
    global $__pinaLinkContext;
    
    if ($repeat) {
        $__pinaLinkContext = $params;
        return;
    }
    
    unset($__pinaLinkContext);

    return $content;
}