<?php

namespace Pina;

class Cache
{

    protected $data = [];

    public function set($key, $expire, &$value)
    {
        $this->data[$key] = [time() + $expire, $value];
        return $value;
    }

    public function has($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        if ($this->data[$key][0] <= time()) {
            return false;
        }

        return true;
    }

    public function get($key)
    {
        $this->data[$key][2] = ($this->data[$key][2] ?? 0) + 1;
        return $this->data[$key][1] ?? null;
    }

}