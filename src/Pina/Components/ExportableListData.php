<?php


namespace Pina\Components;

abstract class ExportableListData extends ListData
{

    protected $filename = '';

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    abstract public function download();

    abstract public function getMimeType();

    abstract public function getExtension();

}