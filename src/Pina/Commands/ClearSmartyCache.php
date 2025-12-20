<?php


namespace Pina\Commands;

use Pina\Command;
use Pina\Config;

class ClearSmartyCache extends Command
{

    protected function execute($input = '')
    {
        $list = [];
        $config = Config::get('app', 'templater');
        $root = $config['compiled'];
        $templates = array_diff(scandir($root), ['.', '..']);
        foreach ($templates as $template) {
            $path = $root . '/' . $template;

            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                unlink($path . '/' . $file);
                $list[] = $path . '/' . $file;
            }
        }
        return implode("\n", $list);
    }

}