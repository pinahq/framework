<?php


namespace Pina\Components;


class DefaultExport extends ExportableListData
{
    protected $export = null;

    public function __construct()
    {
        $this->export = new CSV();
    }

    public function download()
    {
        return $this->export->download();
    }

    public function getMimeType()
    {
        return $this->export->getMimeType();
    }

    public function getExtension()
    {
        return $this->export->getExtension();
    }

}