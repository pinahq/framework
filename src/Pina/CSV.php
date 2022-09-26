<?php

namespace Pina;

class CSV
{

    protected $schema = [];
    protected $delimiter = ',';
    protected $charset = 'utf8';
    protected $enclosure = '"';
    protected $handle = null;

    public function __construct($delimiter, $enclosure, $charset = 'utf8')
    {
        $this->delimiter = $delimiter;
        $this->charset = $charset;
        $this->enclosure = $enclosure;
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    public function download($filename, &$data, $dataCharset = 'utf8')
    {
        header('Content-Type:application/csv;charset=' . $this->charset);
        header('Content-Disposition:attachment;filename="' . $filename . '"');

        $this->handle = fopen("php://output", "r+");
        $this->write($data, $dataCharset);
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    protected function write(&$data, $dataCharset)
    {
        if ($this->schema) {
            $line = Arr::column($this->schema, 1);
            if ($this->charset != $dataCharset) {
                foreach ($line as $k => $v) {
                    $line[$k] = iconv($dataCharset, $this->charset, $v);
                }
            }
            fputcsv($this->handle, $line, $this->delimiter, $this->enclosure);
        }
        foreach ($data as $line) {
            $line = $this->processLine($line);
            if ($this->charset != $dataCharset) {
                foreach ($line as $k => $v) {
                    $line[$k] = iconv($dataCharset, $this->charset, $v);
                }
            }

            fputcsv($this->handle, $line, $this->delimiter, $this->enclosure);
        }
        fclose($this->handle);
    }

    protected function processLine($line)
    {
        if (empty($this->schema)) {
            return $line;
        }
        $r = [];
        foreach ($this->schema as $spec) {
            $r[] = isset($line[$spec[0]]) ? $line[$spec[0]] : '';
        }
        return $r;
    }

}
