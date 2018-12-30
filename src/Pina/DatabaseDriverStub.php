<?php

namespace Pina;

use Pina\Config;
use Pina\Log;

class DatabaseDriverStub implements \Pina\DatabaseDriverInterface
{

    public function __construct()
    {
    }

    public function query($sql)
    {
    }

    public function table($sql)
    {
        return array();
    }

    public function row($sql)
    {
        return array();
    }

    public function col($sql)
    {
        return array();
    }

    public function one($sql)
    {
        return 0;
    }

    public function batch($queries)
    {
    }

    public function num($sql)
    {
        return 0;
    }

    public function insertId()
    {
        return 0;
    }

    public function affectedRows()
    {
        return 0;
    }

    public function escape($str)
    {
        return addslashes($str);
    }

    public function errno()
    {
        return 0;
    }

    public function error()
    {
        return '';
    }

}
