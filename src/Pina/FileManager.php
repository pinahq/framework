<?php

namespace Pina;

class FileManager
{

    protected static $dir = 'attachments';
    protected static $approved = array(
        "jpg", "jpeg", "png", "gif", "ico",
        "xls", "xlsx", "doc", "docx", "pdf", "ppt", "pptx",
        "psd", "crl",
        "zip", "rar", "tgz", "tar",
        "txt",
    );

    protected static $table = '';

    //Path App::uploads().'/'.static::$dir;

    public static function tmpName($ext, $key = false)
    {
        $uniqueId = md5($key?$key:(time().rand()));
        $filename = $uniqueId.'.'.strtolower($ext);
        return $filename;
    }

    public static function newFileName($souce_filename, $ext)
    {
        if (empty($ext)) return $souce_filename;
        
        $postfix = "";
        do
        {
            $filename = $souce_filename.$postfix;
            $filepath = static::getFilePath($filename, $ext);
            $postfix = strval(intval($postfix) + 1);
        } while (file_exists($filepath));
        
        return $filename;
    }

    public static function getFileUrl($filename, $ext = false)
    {
        if (!empty($ext))
        {
            $filename .= ".".$ext;
        }
        
        $static = \Pina\Config::get('app', 'static');

        $r = App::baseUrl();
        if ($static) {
            $r .= trim($static,'/').'/';
        }
        $r .= "uploads/".static::$dir.'/';
        $hash = md5($filename);
        $r .= substr($hash, 0, 2)."/".substr($hash, 2, 2);
        $r .= "/".$filename;

        return $r;
    }

    public static function getFilePath($filename, $ext = false)
    {
        if (!empty($ext))
        {
            $filename .= ".".$ext;
        }

        $r = App::uploads().'/'.static::$dir.'/';
        $hash = md5($filename);
        $r .= substr($hash, 0, 2)."/".substr($hash, 2, 2);
        $r .= "/".$filename;

        return $r;
    }

    public static function upload($item = 'Filedata')
    {
        global $_FILES;

        if (!isset($_FILES[$item]['tmp_name']) || !is_uploaded_file($_FILES[$item]['tmp_name'])) {
            return false;
        }

        $pathinfo = pathinfo($_FILES[$item]['name']);
        $ext = !empty($pathinfo["extension"])?strtolower($pathinfo["extension"]):'';
        //$type = !empty($_FILES[$item]['type']) ? $_FILES[$item]['type'] : '';
        if (!in_array($ext, static::$approved)) {
            return false;
        }

        $souce_filename = strtolower(Token::translit($pathinfo["filename"]));

        $filename = static::newFileName($souce_filename, $ext);

        $filepath = static::getFilePath($filename, $ext);

        static::prepareDir($filename, $ext);
        
        if (!@move_uploaded_file($_FILES[$item]['tmp_name'], $filepath))
        {
            Log::error('image', 'FileManager can not write to file '.$filepath);
            return false;
        }

        return static::prepareData($filename, $ext);
    }

    public static function prepareDir($filename, $ext = false)
    {
        if (!empty($ext))
        {
            $filename .= ".".$ext;
        }

        $r = App::uploads().'/'.static::$dir.'/';
        @mkdir($r, 0777);
        @chmod($r, 0777);

        $hash = md5($filename);
        $first = substr($hash, 0, 2);
        $second = substr($hash, 2, 2);
        @mkdir($r . "/" . $first, 0777);
        @chmod($r, 0777);
        @mkdir($r . "/" . $first . "/" . $second, 0777);
        @chmod($r, 0777);
    }


    public static function prepareFilename($filepath, $filename)
    {
        $sourcePathinfo = pathinfo($filepath);
        $targetPathinfo = pathinfo($filename);

        $sourceExtension = isset($sourcePathinfo['extension']) ? $sourcePathinfo['extension'] : '';
        $targetExtension = isset($targetPathinfo['extension']) ? $targetPathinfo['extension'] : '';

        if (empty($targetExtension) && !empty($sourceExtension) || $targetExtension != $sourceExtension) {
            $filename = $filename . "." . $sourceExtension;
        }

        static::prepareDir($filename);

        return $filename;
    }

    public static function prepareData($filename, $ext = false, $type = '')
    {
        if (empty(static::$table)) return 0;

        $filesize = filesize(static::getFilePath($filename, $ext));

        if (empty($filesize)) {
            return false;
        }

        $data['filename'] = $filename . (!empty($ext) ? ("." . $ext) : "");
        $data['type'] = $type;
        $data['size'] = $filesize;

        $gw = new static::$table;
        $id = $gw->insertGetId($data);

        return $id;
    }

    public static function getAlreadyExists($filepath)
    {
        if (empty(static::$table))
        {
            return false;
        }

        $gw = new static::$table;
        if (!method_exists($gw, 'whereHash')) 
        {
            return false;
        }

        $md5 = md5_file($filepath);
        return $gw->whereHash($md5)->exists();
    }

    public static function saveCopy($filepath, $filename)
    {
        if (empty($filepath))
        {
            return false;
        }

        if (!file_exists($filepath)) {
            return false;
        }

        $filename = static::prepareFilename($filepath, $filename);
        
        if ($id = static::getAlreadyExists($filepath))
        {
            return $id;
        }

        copy($filepath, static::getFilePath($filename));

        return static::prepareData($filename);
    }

    public static function saveUrl($source, $filename)
    {
        if (empty($source)) return;

        $filename = static::prepareFilename($source, $filename);

        $image_content = @file_get_contents($source);
        if (empty($image_content)) return false;

        file_put_contents(static::getFilePath($filename), $image_content);
        unset($image_content);

        return static::prepareData($filename);
    }

    public static function remove($filename)
    {
        @unlink(static::getFilePath($subject, $filename));
    }

}
