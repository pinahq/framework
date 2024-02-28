<?php


namespace Pina\Types;


use Pina\App;
use Pina\Controls\FormControl;
use Pina\Controls\FormSelect;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
use Pina\Controls\NoInput;
use Pina\Data\Field;
use Pina\TableDataGateway;

abstract class DirectoryType implements TypeInterface
{

    abstract public function getVariants();

    abstract public function format($value): string;

    abstract public function normalize($value, $isMandatory);

    abstract public function getSQLType(): string;

    public function setContext($context)
    {
        return $this;
    }

    public function makeControl(Field $field, $value): FormControl
    {
        $input = $this->resolveInput($field);

        if ($field->isStatic()) {
            $value = $this->draw($value);
        }

        $input->setValue($value);
        $input->setDescription($field->getDescription());
        $input->setRequired($field->isMandatory());
        $input->setName($field->getName());
        $input->setTitle($field->getTitle());

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

        $input = $this->makeSelect();
        if ($field->isMultiple()) {
            $input->setMultiple(true);
        }
        return $input;
    }

    public function draw($value): string
    {
        return $this->format($value);
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

    public function getSize(): int
    {
        return 0;
    }

    public function getDefault()
    {
        return null;
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
        return true;
    }

    public function getData($id)
    {
        return null;
    }

    public function setData($id, $value)
    {

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
     * @return FormSelect
     */
    protected function makeSelect()
    {
        /** @var FormSelect $control */
        $control = App::make(FormSelect::class);
        $variants = $this->getVariants();
        if ($this->isPlaceholderNeeded($variants)) {
            array_unshift($variants, ['id' => '', 'title' => $this->getPlaceholder()]);
        }
        $control->setVariants($variants);
        return $control;
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