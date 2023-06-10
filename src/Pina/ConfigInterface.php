<?php

namespace Pina;

interface ConfigInterface
{
    public function get($s, $key = null);
}