<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\Field;
use Pina\Html;

class RecordView extends Control implements InputFactoryInterface
{
    use RecordTrait;

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw()
    {
        return Html::nest('div', $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(), $this->makeAttributes());
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function drawInner()
    {
        return $this->makeRecordFormCompiled($this->record);
    }

    protected function makeRecordFormCompiled(DataRecord $record): RecordFormCompiler
    {
        /** @var RecordFormCompiler $compiler */
        $compiler = App::make(RecordFormCompiler::class);
        $compiler->load($record, $this);

        return $compiler;
    }

    /**
     * @param Field $field
     * @param DataRecord $data
     * @return Control|FormControl
     * @throws \Exception
     */
    public function makeInput(Field $field, DataRecord $record)
    {
        $title = $field->getTitle();
        $key = $field->getName();
        $value = $this->record->getInteractiveValue($key);
        return $this->makeFormStatic()
            ->setName($key)
            ->setTitle($title)
            ->setValue($value);
    }

    /**
     * @return FormStatic
     */
    protected function makeFormStatic()
    {
        return App::make(FormStatic::class);
    }


}