<?php

namespace Pina\Types;

use Pina\Components\Field;

class CallbackType implements TypeInterface
{

    /**
     * @var \Closure $callback
     */
    protected $callback;

    public function setCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    public function makeControl(Field $field, $value)
    {
        return $this->callback($field, $value);
    }

    public function getSize()
    {
        return null;
    }

    public function getDefault()
    {
        return '';
    }

    public function isNullable()
    {
        return true;
    }

    public function getVariants()
    {
        return [];
    }

    public function validate(&$value)
    {
        return null;
    }

}
