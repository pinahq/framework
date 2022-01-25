<?php


namespace Pina\Processors;

class KeyMetaProcessor
{
    protected $key = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function __invoke($meta, $raw)
    {
        $parts = [];
        foreach ($this->key as $k) {
            $parts[] = $raw[$k] ?? '';
        }
        $meta['keys'] = $parts;
        return $meta;
    }
}