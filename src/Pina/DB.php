<?php

namespace Pina;

class DB
{

    private $conn = null;

    public function __construct($conn)
    {
        $this->init($conn);
    }
    
    static public function get($alias = 'default')
    {
        static $dbs = array();

        if (!empty($dbs[$alias])) {
            return $dbs[$alias];
        }

        $configDB = Config::load('db');

        $rc = @mysql_pconnect(
            $configDB[$alias]['host'] . ':' . $configDB[$alias]['port'],
            $configDB[$alias]['user'],
            $configDB[$alias]['pass']
        );

        if (empty($rc) || !in_array(mysql_errno($rc), array(0, 1146))) {
            if (empty($configDB[$alias]['base'])) {
                header("Location: install.php");
                exit;
            }
            Log::error('mysql', mysql_error());
            die();
        }

        mysql_select_db($configDB[$alias]['base'], $rc);
        if (empty($rc) || mysql_errno($rc)) {
            if (empty($configDB[$alias]['base'])) {
                redirect("install.php");
                exit;
            }
            ErrorReporter::notify('mysql', mysql_error());
            die('db access error');
        }

        if ($configDB[$alias]['charset']) {
            mysql_query('SET NAMES ' . $configDB[$alias]['charset'], $rc);
        }

        $db = new DB($rc);
        $dbs[$alias] = $db;

        return $dbs[$alias];
    }

    public function init($conn)
    {
        $this->conn = $conn;
    }

    public function query($sql, $ignore = false)
    {
        if (empty($this->conn)) {
            return false;
        }

        list($msec, $sec) = explode(' ', microtime());
        $s_time = (float) $msec + (float) $sec;

        $rc = mysql_query($sql, $this->conn);
        if (!$rc && !$ignore) {
            ErrorReporter::notify('mysql', mysql_error() . ": " . $sql);
        }

        list($msec, $sec) = explode(' ', microtime());
        $time_total = ((float) $msec + (float) $sec - $s_time);

        if (mysql_errno($this->conn) && !$ignore) {
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
            while ($row = mysql_fetch_assoc($rc)) {
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
            while ($row = mysql_fetch_assoc($rc)) {
                $result [] = $row;
            }
        }

        mysql_free_result($rc);

        return $result;
    }

    public function row($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $r = mysql_fetch_assoc($rc);

        mysql_free_result($rc);

        return $r;
    }

    public function col($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $result = array();

        while ($row = mysql_fetch_row($rc)) {
            if (!isset($row[0])) {
                return false;
            }

            $result [] = $row[0];
        }

        mysql_free_result($rc);

        return $result;
    }

    public function one($sql)
    {
        $rc = $this->query($sql);

        if (empty($rc)) {
            return false;
        }

        $row = mysql_fetch_row($rc);

        mysql_free_result($rc);

        if (!isset($row[0])) {
            return false;
        }

        return $row[0];
    }

    public function num($sql)
    {
        $rc = $this->query($sql);
        $r = mysql_num_rows($rc);
        mysql_free_result($rc);
        return $r;
    }

    public function insertId()
    {
        return mysql_insert_id($this->conn);
    }

    public function affectedRows()
    {
        return mysql_affected_rows($this->conn);
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
            $str = mysql_real_escape_string($str, $this->conn);
        }

        return $str;
    }

    public function outError($sql)
    {
        $errno = $this->errno();
        $error = $this->error();
        $err = empty($errno) ? $error : ($error . ' (' . $errno . ')');
        echo '<div style="background-color: white;">';
        echo '<b><font color="darkred">INVALID SQL:</font></b><font color="black">' . $err . '</font><br />';
        echo '<b><font color="darkred">FAILED QUERY:</font></b><font color="black">' . $sql . '</font><br />';
        echo '</div>';
        flush();
        
        Log::error('mysql', $errno . ': '.$error);
    }

    public function errno()
    {
        return mysql_errno($this->conn);
    }

    public function error()
    {
        return mysql_error($this->conn);
    }

}
