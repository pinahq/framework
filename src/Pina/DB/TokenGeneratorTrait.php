<?php

namespace Pina\DB;

trait TokenGeneratorTrait
{

    /**
     * @return string
     */
    abstract protected function singlePrimaryKeyField(): string;

    /**
     * @return boolean
     */
    abstract public function insert(array $data, string $onDuplicate = '');

    /**
     * @return boolean
     */
    abstract public function put(array $data);

    /**
     * @param array|string $id
     * @return $this
     */
    abstract public function whereId($id, array $context = []);

    abstract public function exists(): bool;

    protected $tokenPattern = '';

    public function setTokenPattern($pattern)
    {
        $this->tokenPattern = trim($pattern);
        return $this;
    }

    public function insertGetId(array $data, string $onDuplicate = '')
    {
        $pk = $this->singlePrimaryKeyField();
        if (!isset($data[$pk])) {
            $data[$pk] = $this->getUniqueToken();
        }

        $this->insert($data, $onDuplicate);

        return $data[$pk];
    }

    public function putGetId(array $data)
    {
        $pk = $this->singlePrimaryKeyField();
        if (!isset($data[$pk])) {
            $data[$pk] = $this->getUniqueToken();
        }

        $this->put($data);

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