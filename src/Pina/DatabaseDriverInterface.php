<?php

namespace Pina;

use Pina\Request;

interface DatabaseDriverInterface
{

    public function query($sql);

    public function table($sql);

    public function row($sql);

    public function col($sql);

    public function one($sql);

    public function batch($queries);

    public function num($sql);

    public function insertId();

    public function affectedRows();

    public function escape($str);

    public function errno();

    public function error();

}
