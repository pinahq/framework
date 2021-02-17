<?php

namespace Pina\Types;

use Closure;
use Pina\Components\Field;

class CallbackType implements TypeInterface
{

    /**
     * @var Closure $callback
     */
    protected $callback;
    protected $context = [];

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    public function makeControl(Field $field, $value, $data = [])
    {
        $callback = $this->callback;
        return $callback($field, $value, $this->context);
    }

    public function format($value)
    {
        return $value;
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
