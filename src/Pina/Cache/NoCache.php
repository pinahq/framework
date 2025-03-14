<?php

namespace Pina\Cache;

class NoCache extends SharedCache
{
    public function set($key, &$value, $seconds = 1)
    {
    }

    public function has($key)
    {
        return false;
    }

    public function get($key)
    {
        return null;
    }
}