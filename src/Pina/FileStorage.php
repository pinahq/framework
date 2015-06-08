<?php

namespace Pina;

class FileStorage
{

    static public function prepareDir($file)
    {
        $pos = 1;
        while ($pos = strpos($file, "/", $pos)) {
            $dir = substr($file, 0, $pos);
            @mkdir($dir, 0777);
            @chmod($dir, 0777);
            $pos = $pos + 1;
        }
    }

    static public function clearDir($dir)
    {
        $d = opendir($dir);
        while (false !== ($file = readdir($d))) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if (is_dir($dir.'/'.$file)) {
                self::clearDir($dir.'/'.$file);
                rmdir($dir.'/'.$file);
            } else {
                unlink($dir.'/'.$file);
            }
        }
    }

}