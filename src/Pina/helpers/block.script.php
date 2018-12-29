<?php

use Pina\App;
use Pina\ResourceManager;
use Pina\StaticResource\Javascript;

function smarty_block_script($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return;
    }
    if (empty($content) && empty($params['src'])) {
        return '';
    }

    if (!empty($params['src']) && !empty($params['module'])) {
        $from = App::path() . "/default/Modules/" . $params['module'] . '/static/' . ltrim($params['src'], '/');
        $to = App::path().'/public/cache/js/' . $params['module'] . '.' . str_replace("/", ".", $params['src']);
        if (!file_exists($to) && file_exists($from)) {
            copy($from, $to);
        }
        $params['src'] = "/cache/js/" . $params['module'] . "." . str_replace("/", ".", $params['src']);
    }
    
    $resourceManager = App::container()->get(ResourceManagerInterface::class);
    
    $resource = new Javascript();
    if (!empty($params['src'])) {
        $resource->setSrc($params['src']);
    } elseif (!empty($content)) {
        $resource->setContent($content);
    }
    $resourceManager->append('js', $resource);
    return '';
}
