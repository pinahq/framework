<?php

use Pina\App;
use Pina\ResourceManager;

function smarty_block_style($params, $content, &$view, &$repeat)
{
    if ($repeat) {
        return;
    }
    if (empty($content) && empty($params['src'])) {
        return '';
    }

    if (!empty($params['src']) && !empty($params['module'])) {
        $from = App::path() . "/default/Modules/" . $params['module'] . '/static/' . ltrim($params['src'], '/');
        $to = App::path().'/public/cache/css/' . $params['module'] . '.' . str_replace("/", ".", $params['src']);
        if (!file_exists($to) && file_exists($from)) {
            copy($from, $to);
        }
        $params['src'] = "/cache/css/" . $params['module'] . "." . str_replace("/", ".", $params['src']);
    }

    if (!empty($params['src'])) {
        ResourceManager::append('css', '<link rel="stylesheet" href="' . $params['src'] . '" />');
    } elseif (!empty($content)) {
        ResourceManager::append('css', $content);
    }
    return '';
}
