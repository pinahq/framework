<?php

namespace Pina;

use Closure;

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

    public function version(): int;

    /**
     * Выполнение функции в рамках одной транзакции
     * @param Closure $closure
     * @return mixed
     */
    public function transaction($closure);

    /**
     * Начать выполнение транзакции
     * @return void
     */
    public function startTransaction();

    /**
     * Завершить транзакцию
     * @return void
     */
    public function commit();

    /**
     * Откатить транзакцию
     * @return void
     */
    public function rollback();

}