<?php


namespace Pina\Controls;


class AttributedBlock
{
    /**
     * @var string[]
     */
    protected $classes = [];

    /**
     * @var string[]
     */
    protected $attributes = [];

    /**
     * @param string $c
     * @return $this
     */
    public function addClass($c)
    {
        $this->classes[] = $c;
        return $this;
    }

    /**
     * @param string|string[] $additional
     * @return string|null
     */
    protected function makeClass($additional = null)
    {
        $classes = $this->classes;
        if (!is_null($additional)) {
            $classes = array_merge($classes, is_array($additional) ? $additional : [$additional]);
        }
        return count($classes) > 0 ? implode(' ', $classes) : null;
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function makeAttributes($attributes = [])
    {
        $class = !empty($attributes['class']) ? $attributes['class'] : null;
        $base = array_merge(['class' => $this->makeClass($class)], $this->getAttributes());
        unset($attributes['class']);
        if (empty($base['class'])) {
            unset($base['class']);
        }

        return array_merge($base, $attributes);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setDataAttribute($key, $value)
    {
        $this->attributes['data-' . $key] = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}