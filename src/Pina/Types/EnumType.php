<?php

namespace Pina\Types;

use Pina\App;
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
        return $this;
    }

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        $star = $field->isMandatory() ? ' *' : '';
        return App::make(FormSelect::class)
                ->setName($field->getKey())
                ->setTitle($field->getTitle() . $star)
                ->setValue($value)
                ->setVariants($this->variants);
    }

    public function format($value)
    {
        foreach ($this->variants as $v) {
            if ($v['id'] == $value) {
                return $v['title'] ?? '';
            }
        }

        return $value;
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
