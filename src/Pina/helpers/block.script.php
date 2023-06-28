<?php

use Pina\App;

function smarty_block_script($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return '';
    }

    if (!empty($params['src'])) {
        App::assets()->addScript($params['src']);
    } elseif (!empty($content)) {
        App::assets()->addScriptContent($content);
    }

    return '';
}
