<?php

namespace Pina\Types;

use Closure;
use Pina\Data\Field;

use Pina\TableDataGateway;

use function Pina\__;

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

    public function normalize($value, $isMandatory)
    {
        if (empty($value) && $isMandatory) {
            throw new ValidateException(__("Укажите значение"));
        }
        return $value;
    }

    public function getSQLType()
    {
        return 'varchar(128)';
    }

    public function filter(TableDataGateway $query, $key, $value)
    {
        return $query->whereBy($key, $value);
    }

}
