<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormControl;
use Pina\Controls\FormInput;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Controls\NoInput;
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
        $input = $this->resolveInput($field);
        $input->setName($field->getName());
        $input->setTitle($field->getTitle());
        $input->setDescription($field->getDescription());
        $input->setRequired($field->isMandatory());

        if ($field->isStatic()) {
            $value = $this->draw($value);
        }
        $input->setValue($value);
        return $input;
    }

    protected function resolveInput(Field $field): FormControl
    {
        if ($field->isStatic() && $field->isHidden()) {
            return $this->makeNoInput();
        }

        if ($field->isStatic()) {
            return $this->makeStatic();
        }

        if ($field->isHidden()) {
            return $this->makeHidden();
        }

        return $this->makeInput();
    }

    public function format($value): string
    {
        return is_null($value) ? '-' : $value;
    }

    public function draw($value): string
    {
        return $this->format($value);
    }

    public function play($value): string
    {
        return $this->draw($value);
    }

    public function getSize(): int
    {
        return 11;
    }

    public function getDefault()
    {
        return 0;
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function isSearchable(): bool
    {
        return false;
    }

    public function isFiltrable(): bool
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

    public function getSQLType(): string
    {
        return "int(" . $this->getSize() . ")";
    }

    public function filter(TableDataGateway $query, $key, $value): void
    {
        $fields = is_array($key) ? $key : [$key];
        if (!$query->hasAllFields($fields)) {
            return;
        }
        $query->whereBy($fields, $value);
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

    protected function makeNoInput()
    {
        return App::make(NoInput::class);
    }

}