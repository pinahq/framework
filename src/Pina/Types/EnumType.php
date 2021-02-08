<?php

namespace Pina\Types;

use Pina\Controls\FormSelect;
use Pina\Components\Field;
use function Pina\__;

class EnumType implements TypeInterface
{
    
    /**
     * @var array $variants
     */
    protected $variants;
    
    public function setVariants($variants)
    {
        $this->variants = $variants;
    }

    public function makeControl(Field $field, $value)
    {
        return \Pina\App::make(FormSelect::class)
            ->setName($field->getKey())
            ->setTitle($field->getTitle())
            ->setValue($value)
            ->setVariants($this->variants);
    }
    
    public function getSize()
    {
        return null;
    }

    public function getDefault()
    {
        return isset($this->variants[0]['id']) ? $this->variants[0]['id'] : '';
    }

    public function isNullable()
    {
        return false;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function validate(&$value)
    {
        $ids = array_column($this->variants, 'id');
        if (!in_array($value, $ids)) {
            return __("Выберите значение");
        }

        return null;
    }

}
