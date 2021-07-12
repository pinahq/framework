<?php

namespace Pina\Types;

use function Pina\__;

abstract class ConfigurableType implements TypeInterface
{

    protected $nullable = false;
    protected $size = 0;
    protected $default = '';
    protected $variants = [];

    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isNullable()
    {
        return $this->nullable == true;
    }

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setVariants($variants)
    {
        $this->variants = $variants;
        return $this;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function normalize($value, $isMandatory)
    {
        if (empty($value) && $isMandatory) {
            throw new ValidateException(__("Укажите значение"));
        }

        if (!empty($this->variants)) {
            if (!in_array($value, array_column($this->variants, 'id'))) {
                throw new ValidateException(__("Выберите вариант"));
            }
        }

        if (empty($value)) {
            return $this->default;
        }

        return $value;
    }

    public function getSQLType()
    {
        return 'varchar(128)';
    }

}
