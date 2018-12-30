<?php

use Pina\App;
use Pina\StaticResource\Style;

function smarty_block_style($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return '';
    }

    $resource = new Style();
    if (!empty($params['src'])) {
        $resource->setSrc($params['src']);
    } elseif (!empty($content)) {
        $resource->setContent($content);
    } else {
        return '';
    }
    App::container()->get(\Pina\ResourceManagerInterface::class)->append($resource);
    return '';
}
