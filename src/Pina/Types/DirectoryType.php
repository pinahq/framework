<?php


namespace Pina\Types;


use Pina\App;
use Pina\Controls\FormSelect;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Data\Field;
use Pina\TableDataGateway;

abstract class DirectoryType implements TypeInterface
{

    abstract public function getVariants();

    abstract public function format($value);

    abstract public function normalize($value, $isMandatory);

    abstract public function getSQLType();

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value)
    {
        $variants = $this->getVariants();
        if ($this->isPlaceholderNeeded($variants)) {
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
            ->setRequired($field->isMandatory())
            ->setName($field->getKey())
            ->setTitle($field->getTitle());
    }



    protected function isPlaceholderNeeded(&$variants)
    {
        $ids = array_column($variants, 'id');
        return !in_array('', $ids);
    }

    protected function getPlaceholder()
    {
        return '';
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

    public function getData($id)
    {
        return null;
    }

    public function setData($id, $value)
    {

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