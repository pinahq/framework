<?php

namespace Pina;

$root = App::templaterCompiled();
echo $root . "\n";
$templates = array_diff(scandir($root), ['.', '..']);
foreach ($templates as $template) {
    $path = $root . '/' . $template;
    echo $path . "\n";

    $files = array_diff(scandir($path), ['.', '..']);
    foreach ($files as $file) {
        echo 'rm ' . $path . '/' . $file . "\n";
        unlink($path . '/' . $file);
    }
}

