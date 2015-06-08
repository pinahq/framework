<?php

use Pina\Place;

function smarty_block_content($params, $content, &$view, &$repeat)
{
    if (empty($params['name'])) {
        return '';
    }

    if (!empty($params['name'])) {
        Place::set($params['name'], $content);
    }
    return '';
}