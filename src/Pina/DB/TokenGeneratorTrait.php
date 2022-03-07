<?php

namespace Pina\DB;

trait TokenGeneratorTrait
{

    /**
     * @return string
     */
    abstract public function primaryKey();

    /**
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    abstract public function insert($data, $fields = false);

    /**
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    abstract public function put($data, $fields = false);

    /**
     * @param array|string $id
     * @return $this
     */
    abstract public function whereId($id);

    /**
     * @return bool
     */
    abstract public function exists();

    protected $tokenPattern = '';

    public function setTokenPattern($pattern)
    {
        $this->tokenPattern = trim($pattern);
        return $this;
    }

    public function insertGetId($data = array(), $fields = false)
    {
        $pk = $this->primaryKey();
        if (!isset($data[$pk])) {
            $data[$pk] = $this->getUniqueToken();
        }

        $this->insert($data, $fields);

        return $data[$pk];
    }

    public function putGetId($data = array(), $fields = false)
    {
        $pk = $this->primaryKey();
        if (!isset($data[$pk])) {
            $data[$pk] = $this->getUniqueToken();
        }

        $this->put($data, $fields);

        return $data[$pk];
    }

    protected function getUniqueToken()
    {
        $token = '';
        $attempts = 0;
        do {
            if ($this->tokenPattern && $attempts < 10) {
                $token = $this->generateByPattern($this->tokenPattern);
            } else {
                $token = $this->generate();
            }
            $attempts++;
        } while ($this->whereId($token)->exists());

        return $token;
    }

    public function generateByPattern($pattern)
    {
        $chars = "ABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $clen = strlen($chars) - 1;
        $token = '';
        $patternLength = strlen($pattern);
        for ($i = 0; $i < $patternLength; $i++) {
            $v = $pattern[$i];
            switch ($v) {
                case 'Z':
                    $token .= $chars[mt_rand(0, $clen)];
                    break;
                default:
                    $token .= $v;
                    break;
            }
        }
        return $token;
    }

    public function generate()
    {
        $chars = "ABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $length = mt_rand(8, 32);

        $clen = strlen($chars) - 1;
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0, $clen)];
        }

        return $code;
    }

}