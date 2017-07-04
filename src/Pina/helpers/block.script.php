<?php

use Pina\App;
use Pina\ResourceManager;

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

    if (!empty($params['src'])) {
        $static = \Pina\Config::get('app', 'static');
        ResourceManager::append('js', '<script src="' . rtrim($static, '/') . '/'. ltrim($params['src'], '/') . '" type="text/javascript"></script>');
    } elseif (!empty($content)) {
        ResourceManager::append('js', $content);
    }
    return '';
}
