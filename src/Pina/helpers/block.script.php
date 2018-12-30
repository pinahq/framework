<?php

use Pina\App;
use Pina\StaticResource\Script;

function smarty_block_script($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return '';
    }

    $resource = new Script();
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
