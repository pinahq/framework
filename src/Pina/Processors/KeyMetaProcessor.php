<?php


namespace Pina\Processors;

/**
 * Дополняет мета-данные данными о составе ключа
 */
class KeyMetaProcessor
{
    /** @var string[] */
    protected $key = [];

    /**
     * @param string[] $key Перечисление полей ключа
     */
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