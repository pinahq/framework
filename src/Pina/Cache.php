<?php

namespace Pina;

class Cache
{

    protected $data = [];

    public function set($key, $expired, $value)
    {
        $this->data[$key] = [time() + $expired, $value];
    }

    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }

        list($expired, $value) = $this->data[$key];
        if ($expired < time()) {
            return null;
        }

        return $value;
    }

}