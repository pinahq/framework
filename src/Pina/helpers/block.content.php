<?php

use Pina\Place;

function smarty_block_content($params, $content, &$view, &$repeat)
{
    if (empty($params['name'])) {
        return '';
    }
    
    \Pina\Request::setPlace($params['name'], $content);
    return '';
}