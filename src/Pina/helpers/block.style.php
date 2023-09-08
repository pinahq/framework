<?php

use Pina\App;

function smarty_block_style($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return '';
    }

    if (!empty($params['src'])) {
        App::assets()->addStyle($params['src']);
    } elseif (!empty($content)) {
        App::assets()->addStyleContent($content);
    }

    return '';
}
