<?php


namespace Pina\Export;

use Pina\Data\DataTable;

class DefaultExport extends ExportableDataTable
{
    protected $export = null;

    public function __construct()
    {
        $this->export = new CSV();
    }

    public function load(DataTable $data)
    {
        $this->export->load($data);

    }

    public function download()
    {
        $this->export->download();
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