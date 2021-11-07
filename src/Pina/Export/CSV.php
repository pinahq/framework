<?php


namespace Pina\Export;


class CSV extends ExportableDataTable
{
    protected $delimiter = ',';
    protected $enclosure = '"';
    protected $charset = 'utf8';
    protected $dataCharset = 'utf8';
    protected $handle = null;

    public function getMimeType()
    {
        return 'application/csv';
    }

    public function getExtension()
    {
        return 'csv';
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function setDataCharset($charset)
    {
        $this->dataCharset = $charset;
        return $this;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    public function download()
    {
        $this->startDownload();
        $this->write();
        $this->finish();
    }

    private function startDownload()
    {
        header('Content-Type:' . $this->getMimeType() . ';charset=' . $this->charset);
        header('Content-Disposition:attachment;filename="' . $this->filename . '"');
        $this->handle = fopen("php://output", "r+");
    }

    private function finish()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    protected function write()
    {
        $this->writeHeader();
        $data = $this->data->getTextData();
        foreach ($data as $line) {
            $this->writeLine($this->data->getSchema()->makeFlatLine($line));
        }
    }

    protected function writeHeader()
    {
        $this->writeLine($this->data->getSchema()->getFieldTitles());
    }

    protected function writeLine($line)
    {
        if ($this->charset != $this->dataCharset) {
            foreach ($line as $k => $v) {
                $line[$k] = iconv($this->dataCharset, $this->charset, $v);
            }
        }
        fputcsv($this->handle, $line, $this->delimiter, $this->enclosure);
    }
}