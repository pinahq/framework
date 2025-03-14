<?php

namespace Pina\Cache;

interface CacheInterface
{
    public function set($key, &$value, $seconds = 1);
    public function has($key);
    public function get($key);
}