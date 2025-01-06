<?php

namespace Pina\Cache;

class Cache
{

    protected $data = [];

    public function set($key, &$value, $seconds = 1)
    {
        $this->data[$key] = [time() + $seconds, $value, 0];
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
        $this->data[$key][2]++;
        return $this->data[$key][1] ?? null;
    }

}