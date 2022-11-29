<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormInput;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Data\Field;

use Pina\TableDataGateway;

use function Pina\__;
use function sprintf;

class StringType implements TypeInterface
{

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        $input = $field->isStatic()
            ? $this->makeStatic()
            : ($field->isHidden() ? $this->makeHidden() : $this->makeInput()->setType('text'));

        $input->setName($field->getKey());
        $input->setTitle($field->getTitle());
        $input->setValue($value);
        $input->setDescription($field->getDescription());
        $input->setRequired($field->isMandatory());
        return $input;
    }

    public function format($value)
    {
        return $value;
    }

    public function getSize()
    {
        return 512;
    }

    public function getDefault()
    {
        return '';
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
        $value = trim($value);

        if (empty($value) && $isMandatory) {
            throw new ValidateException(__("Укажите значение"));
        }

        $size = $this->getSize();
        if (strlen($value) > $size) {
            throw new ValidateException(sprintf(__("Укажите значение короче. Максимальная длина %s символов"), $size));
        }

        return $value;
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
        return "varchar(" . $this->getSize() . ")";
    }

    public function filter(TableDataGateway $query, string $key, $value)
    {
        return $query->whereLike($key, '%' . $value . '%');
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
