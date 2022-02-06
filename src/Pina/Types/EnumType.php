<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormSelect;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Data\Field;

use Pina\TableDataGateway;

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

        $variants = $this->variants;
        if ($this->isPlaceholderNeeded()) {
            array_unshift($variants, ['id' => '', 'title' => $this->getPlaceholder()]);
        }

        $control = $field->isStatic()
            ? $this->makeStatic()->setValue($this->format($value))
            : (
            $field->isHidden()
                ? $this->makeHidden()->setValue($value)
                : $this->makeSelect()->setVariants($variants)->setValue($value)
            );

        return $control
            ->setName($field->getKey())
            ->setTitle($field->getTitle() . $star);
    }

    protected function isPlaceholderNeeded()
    {
        $ids = array_column($this->variants, 'id');
        return !in_array('', $ids);
    }

    protected function getPlaceholder()
    {
        return '';
    }

    public function format($value)
    {
        foreach ($this->variants as $v) {
            if ($v['id'] == $value) {
                return isset($v['title']) ? $v['title'] : '';
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
        return null;
    }

    public function isNullable()
    {
        return false;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function normalize($value, $isMandatory)
    {
        $ids = array_column($this->variants, 'id');
        if (!in_array($value, $ids)) {
            throw new ValidateException(__("Выберите значение"));
        }

        return $value;
    }

    public function getSQLType()
    {
        $variants = array_column($this->variants, 'id');
        return "enum('" . implode("','", $variants) . "')";
    }

    public function filter(TableDataGateway $query, $key, $value)
    {
        return $query->whereBy($key, $value);
    }

    /**
     * @return FormSelect
     */
    protected function makeSelect()
    {
        return App::make(FormSelect::class);
    }

    /**
     * @return FormStatic
     */
    protected function makeStatic()
    {
        return App::make(FormStatic::class);
    }

    /**
     * @return HiddenInput
     */
    protected function makeHidden()
    {
        return App::make(HiddenInput::class);
    }


}
