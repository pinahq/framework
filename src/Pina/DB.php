<?php

namespace Pina;

class DB
{

    private $conn = null;
    private $alias = '';

    public function __construct($conn, $alias)
    {
        $this->init($conn, $alias);
    }

    public static function get($alias = 'default')
    {
        static $dbs = array();

        if (!empty($dbs[$alias])) {
            return $dbs[$alias];
        }

        $rc = self::getConnection($alias);

        $dbs[$alias] = new DB($rc, $alias);

        return $dbs[$alias];
    }

    private static function getConnection($alias)
    {
        $configDB = Config::load('db');

        $rc = mysqli_connect(
            "p:" . $configDB[$alias]['host'], $configDB[$alias]['user'], $configDB[$alias]['pass'], $configDB[$alias]['base'], $configDB[$alias]['port']
        );

        if (empty($rc) || !in_array(mysqli_errno($rc), array(0, 1146))) {
            if (empty($configDB[$alias]['base'])) {
                exit;
            }
            Log::error('mysql', mysqli_error());
            die();
        }

        if ($configDB[$alias]['charset']) {
            mysqli_query($rc, 'SET NAMES ' . $configDB[$alias]['charset']);
        }

        return $rc;
    }

    public function init($conn, $alias)
    {
        $this->conn = $conn;
        $this->alias = $alias;
    }

    private function reconnect()
    {
        mysqli_close($this->conn);
        $this->conn = self::getConnection($this->alias);
    }

    public function query($sql, $ignore = false, $recursive = false)
    {
        if (empty($this->conn)) {
            return false;
        }

        list($msec, $sec) = explode(' ', microtime());
        $s_time = (float) $msec + (float) $sec;

        $rc = mysqli_query($this->conn, $sql);
        if (!$rc && !$recursive && preg_match('/Lost connection|server has gone away/i', mysqli_error($this->conn))) {
            Log::error('mysql', 'TRY TO RECONNECT AFTER SERVER HAS GONE AWAY');
            $this->reconnect();
            return $this->query($sql, $ignore, true);
        }

        list($msec, $sec) = explode(' ', microtime());
        $time_total = ((float) $msec + (float) $sec - $s_time);

        if (mysqli_errno($this->conn) && !$ignore) {
            $this->outError($sql);
        }

        return $rc;
    }

    public function table($sql, $key = '', $add = true, $removeKey = false)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $result = array();

        if (!empty($key)) {
            while ($row = mysqli_fetch_assoc($rc)) {
                $id = $row[$key];
                if ($removeKey) {
                    unset($row[$key]);
                }
                if ($add) {
                    $result[$id] [] = $row;
                } else {
                    $result[$id] = $row;
                }
            }
        } else {
            while ($row = mysqli_fetch_assoc($rc)) {
                $result [] = $row;
            }
        }

        mysqli_free_result($rc);

        return $result;
    }

    public function row($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $r = mysqli_fetch_assoc($rc);

        mysqli_free_result($rc);

        return $r;
    }

    public function col($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $result = array();

        while ($row = mysqli_fetch_row($rc)) {
            if (!isset($row[0])) {
                continue;
            }

            $result [] = $row[0];
        }

        mysqli_free_result($rc);

        return $result;
    }

    public function one($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $row = mysqli_fetch_row($rc);

        mysqli_free_result($rc);

        if (!isset($row[0])) {
            return false;
        }

        return $row[0];
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

    public function loop($sql)
    {
        return new DBLoop($this->query($sql));
    }

    public function escape($str)
    {
        if (is_array($str)) {
            foreach ($str as $k => $tmp) {
                $str[$k] = $this->escape($str[$k]);
            }
        } else {
            $str = mysqli_real_escape_string($this->conn, $str);
        }

        return $str;
    }

    public function outError($sql)
    {
        $errno = $this->errno($this->conn);
        $error = $this->error($this->conn);
        $err = empty($errno) ? $error : ($error . ' (' . $errno . ')');
        echo '<div style="background-color: white;">';
        echo '<b><font color="darkred">INVALID SQL:</font></b><font color="black">' . $err . '</font><br />';
        echo '<b><font color="darkred">FAILED QUERY:</font></b><font color="black">' . $sql . '</font><br />';
        echo '</div>';
        flush();

        Log::error('mysql', $errno . ': ' . $error . '; ' . $sql);
    }

    public function errno()
    {
        return mysqli_errno($this->conn);
    }

    public function error()
    {
        return mysqli_error($this->conn);
    }

}
