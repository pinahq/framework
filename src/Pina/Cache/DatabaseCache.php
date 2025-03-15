<?php

namespace Pina\Cache;

class DatabaseCache extends SharedCache
{
    public function set($key, &$value, $seconds = 1)
    {
        DatabaseCacheGateway::instance()->put(['id' => md5($key), 'data' => serialize($value), 'ttl' => $seconds]);
        return $value;
    }

    public function has($key)
    {
        return DatabaseCacheGateway::instance()
            ->whereId(md5($key))
            ->whereNotExpired()
            ->exists();
    }

    public function get($key)
    {
        return unserialize(DatabaseCacheGateway::instance()->whereId(md5($key))->whereNotExpired()->value('data'));
    }
}