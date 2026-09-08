<?php

namespace Pina\Cache;

use Pina\Container\SingletonTrait;

abstract class SharedCache implements CacheInterface
{
    use SingletonTrait;
}