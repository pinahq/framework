<?php

namespace Pina\Export;

use Pina\Data\DataTable;

/**
 * Временное решение для экспорта, пока уничтожаем компоненты в пользу контролов
 */
abstract class ExportableDataTable
{
    protected $filename = '';

    /** @var DataTable */
    protected $data;

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    public function load(DataTable $data)
    {
        $this->data = $data;
    }

    abstract public function download();

    abstract public function getMimeType();

    abstract public function getExtension();
}