<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormControl;
use Pina\Controls\FormInput;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Data\Field;
use Pina\TableDataGateway;

use function Pina\__;

class IntegerType implements TypeInterface
{

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value): FormControl
    {
        $control = $field->isStatic()
            ? $this->makeStatic()
            : ($field->isHidden() ? $this->makeHidden() : $this->makeInput()->setType('text'));

        if ($field->isStatic()) {
            $value = $this->format($value);
        }

        return $control
            ->setName($field->getKey())
            ->setValue($value)
            ->setTitle($field->getTitle())
            ->setDescription($field->getDescription())
            ->setRequired($field->isMandatory());
    }

    public function format($value)
    {
        return is_null($value) ? '-' : $value;
    }

    public function getSize()
    {
        return 11;
    }

    public function getDefault()
    {
        return 0;
    }

    public function isNullable()
    {
        return false;
    }

    public function getVariants()
    {
        return [];
    }

    public function normalize($value, $isMandatory)
    {
        if (strval(intval($value)) != strval($value)) {
            throw new ValidateException(__("Укажите целое число"));
        }

        return intval($value);
    }

    public function getData($id)
    {
        return null;
    }

    public function setData($id, $value)
    {

    }

    public function getSQLType()
    {
        return "int(" . $this->getSize() . ")";
    }

    public function filter(TableDataGateway $query, string $key, $value): void
    {
        if (!$query->hasField($query)) {
            return;
        }
        $query->whereBy($key, $value);
    }

    /**
     * @return FormInput
     */
    protected function makeInput()
    {
        return App::make(FormInput::class);
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