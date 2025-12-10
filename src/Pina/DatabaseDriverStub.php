<?php

namespace Pina;


use Pina\Cache\CacheInterface;

class DatabaseDriverStub extends DatabaseDriver
{

    public function __construct()
    {
    }

    public function query(string $sql)
    {
    }

    public function table(string $sql, int $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        return array();
    }

    public function row(string $sql, int $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        return array();
    }

    public function col(string $sql, int $cacheSeconds = 0, ?CacheInterface $cache = null): array
    {
        return array();
    }

    public function one(string $sql, int $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        return 0;
    }

    public function batch(array $queries)
    {
    }

    public function num(string $sql)
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

    public function version(): int
    {
        return 0;
    }

    public function transaction($closure)
    {
        return $closure();
    }

    public function startTransaction()
    {
    }

    public function commit()
    {
    }

    public function rollback()
    {
    }

}
