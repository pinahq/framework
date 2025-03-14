<?php

namespace Pina\Cache;

class CacheSlot
{

    protected $cache;
    protected $key = '';

    public function __construct(CacheInterface $cache, $key)
    {
        $this->cache = $cache;
        $this->key = $key;
    }

    public function filled()
    {
        return $this->cache->has($this->key);
    }

    public function get()
    {
        return $this->cache->get($this->key);
    }

    public function set(&$value, $seconds = 1)
    {
        return $this->cache->set($this->key, $value, $seconds);
    }

}