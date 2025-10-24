<?php

namespace Pina;

use Exception;
use Pina\Cache\CacheInterface;
use Pina\Cache\CacheSlot;
use Pina\Cache\StaticCache;
use RuntimeException;

class DatabaseDriver implements DatabaseDriverInterface
{

    protected $conn = null;

    public function __construct()
    {
        $this->connect();
    }

    protected function connect()
    {
        $config = Config::load('db');

        $this->conn = mysqli_connect(
            $config['host'], $config['user'], $config['pass'], $config['base'], $config['port']
        );

        if (empty($this->conn)) {
            throw new RuntimeException('Can`t connect to database');
        }

        if ($config['charset']) {
            $this->query('SET NAMES ' . $this->escape($config['charset']));
        }

        if (!empty($config['timezone'])) {
            $this->query("SET time_zone = '" . $this->escape($config['timezone'])."'");
        }
    }

    protected function reconnect()
    {
        mysqli_close($this->conn);
        $this->connect();
    }

    public function query($sql)
    {
        return $this->doQuery($sql, false);
    }

    protected function doQuery($sql, $retry)
    {
        static $number = 0;
        static $total = 0;

        list($msec, $sec) = explode(' ', microtime());
        $startTime = (float) $msec + (float) $sec;

        $rc = mysqli_query($this->conn, $sql);
        if (!$rc && !$retry && preg_match('/Lost connection|server has gone away/i', mysqli_error($this->conn))) {
            Log::error('mysql', 'TRY TO RECONNECT AFTER SERVER HAS GONE AWAY');
            $this->reconnect();
            return $this->doQuery($sql, true);
        }

        list($msec, $sec) = explode(' ', microtime());
        $totalTime = (float) $msec + (float) $sec - $startTime;

        Log::debug('mysql', round($totalTime, 4) . ' ' . $sql);

        if ($this->errno()) {
            throw new InternalErrorException($this->error() . '; Failed query: ' . $sql, $this->errno());
        }

        return $rc;
    }

    protected function cache(string $key, ?CacheInterface $cache = null): CacheSlot
    {
        return new CacheSlot($cache ? $cache : App::load(StaticCache::class), $key);
    }

    public function table($sql, $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        if ($cacheSeconds > 0) {
            $cacheSlot = $this->cache('db:table:'.$sql, $cache);
            if ($cacheSlot->filled()) {
                return $cacheSlot->get();
            }
        }

        $rc = $this->query($sql);

        $result = array();
        while ($row = mysqli_fetch_assoc($rc)) {
            $result [] = $row;
        }

        mysqli_free_result($rc);

        if ($cacheSeconds > 0) {
            $cacheSlot->set($result, $cacheSeconds);
        }

        return $result;
    }

    public function row($sql, $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        if ($cacheSeconds > 0) {
            $cacheSlot = $this->cache('db:row:'.$sql, $cache);
            if ($cacheSlot->filled()) {
                return $cacheSlot->get();
            }
        }

        $rc = $this->query($sql);

        $r = mysqli_fetch_assoc($rc);

        mysqli_free_result($rc);

        if ($cacheSeconds > 0) {
            $cacheSlot->set($r, $cacheSeconds);
        }

        return $r;
    }

    public function col($sql, $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        if ($cacheSeconds > 0) {
            $cacheSlot = $this->cache('db:col:'.$sql, $cache);
            if ($cacheSlot->filled()) {
                return $cacheSlot->get();
            }
        }

        $rc = $this->query($sql);

        $result = array();

        while ($row = mysqli_fetch_row($rc)) {
            if (!isset($row[0])) {
                continue;
            }

            $result [] = $row[0];
        }

        mysqli_free_result($rc);

        if ($cacheSeconds > 0) {
            $cacheSlot->set($result, $cacheSeconds);
        }

        return $result;
    }

    public function one($sql, $cacheSeconds = 0, ?CacheInterface $cache = null)
    {
        if ($cacheSeconds > 0) {
            $cacheSlot = $this->cache('db:col:'.$sql, $cache);
            if ($cacheSlot->filled()) {
                return $cacheSlot->get();
            }
        }

        $rc = $this->query($sql);

        $row = mysqli_fetch_row($rc);

        mysqli_free_result($rc);

        if ($cacheSeconds > 0) {
            $cacheSlot->set($row[0], $cacheSeconds);
        }

        return $row[0] ?? null;
    }

    public function batch($queries)
    {
        if (!is_array($queries)) {
            return;
        }

        foreach ($queries as $q) {
            if (empty($q)) {
                continue;
            }
            $this->query($q);
        }
    }

    public function num($sql)
    {
        $rc = $this->query($sql);
        $r = mysqli_num_rows($rc);
        mysqli_free_result($rc);
        return $r;
    }

    public function insertId()
    {
        return mysqli_insert_id($this->conn);
    }

    public function affectedRows()
    {
        return mysqli_affected_rows($this->conn);
    }

    public function escape($str)
    {
        if (is_array($str)) {
            foreach ($str as $k => $tmp) {
                $str[$k] = $this->escape($str[$k]);
            }
        } elseif (!is_null($str)) {
            $str = mysqli_real_escape_string($this->conn, $str ?? "");
        }

        return $str;
    }

    public function errno()
    {
        return mysqli_errno($this->conn);
    }

    public function error()
    {
        return mysqli_error($this->conn);
    }

    public function version(): int
    {
        return mysqli_get_server_version($this->conn);
    }

    /**
     * @param \Closure $closure
     * @return mixed
     * @throws Exception
     */
    public function transaction($closure)
    {
        $this->startTransaction();
        try {
            $r = $closure();
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
        return $r;
    }

    public function startTransaction()
    {
        $this->query("START TRANSACTION");
    }

    public function commit()
    {
        $this->query("COMMIT");
    }

    public function rollback()
    {
        $this->query("ROLLBACK");
    }

}
