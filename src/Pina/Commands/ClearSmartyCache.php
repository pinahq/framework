<?php


namespace Pina\Commands;


use Pina\App;
use Pina\Command;

class ClearSmartyCache extends Command
{

    protected function execute($input = '')
    {
        $list = [];
        $root = App::templaterCompiled();
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