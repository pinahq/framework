<?php


namespace Pina\Types;


use Pina\App;
use Pina\Controls\FormControl;
use Pina\Controls\FormSelect;
use Pina\Controls\FormStatic;
use Pina\Controls\HiddenInput;
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
        $variants = $this->getVariants();
        if ($this->isPlaceholderNeeded($variants)) {
            array_unshift($variants, ['id' => '', 'title' => $this->getPlaceholder()]);
        }

        $control = $field->isStatic()
            ? $this->makeStatic()->setValue($this->draw($value))
            : (
            $field->isHidden()
                ? $this->makeHidden()->setValue($value)
                : $this->makeSelect()->setVariants($variants)->setValue($value)
            );

        return $control
            ->setDescription($field->getDescription())
            ->setRequired($field->isMandatory())
            ->setName($field->getName())
            ->setTitle($field->getTitle());
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