<?php


namespace Pina\Model;


class TagOptions
{
    protected $classes;

    protected $options = [];

    public function __construct()
    {
        $this->classes = new WordSet();
    }

    public function classes()
    {
        return $this->classes;
    }

    public function set(string $key, string $value)
    {
        if ($key == 'class') {
            $this->classes->clear();
            $this->classes->add($value);
            return;
        }

        $this->options[$key] = trim($value);
    }

    public function get($key): string
    {
        if ($key == 'class') {
            return $this->classes->__toString();
        }

        return $this->options[$key] ?? '';
    }

    public function toArray(): array
    {
        $classes = $this->classes->__toString();
        if (empty($classes)) {
            return $this->options;
        }

        return array_merge(['class' => $classes], $this->options);
    }

}