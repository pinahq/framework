<?php

namespace Pina;

class TemporaryFile extends FileManager
{
    protected $dir = '';
    protected $path = PATH_TEMP;

    protected $approved = array(
        "jpg", "jpeg", "png", "gif", "ico",
        "xls", "xlsx", "doc", "docx", "pdf", "ppt", "pptx",
        "psd", "crl",
        "zip", "rar", "tgz", "tar",
        "txt",
    );

    public function __construct($approved = false)
    {
        if (!is_array($approved)) {
            return false;
        }
        if (empty($approved)) {
            return false;
        }

        $this->approved = $approved;
    }

    public function add($data)
    {
        return $data;
    }
}
