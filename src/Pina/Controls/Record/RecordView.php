<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Control;
use Pina\Controls\ControlContainer;
use Pina\Controls\Form\FormControl;
use Pina\Controls\Form\FormStatic;
use Pina\Controls\Form\InputFactoryInterface;
use Pina\Data\DataRecord;
use Pina\Data\Field;
use Pina\Data\Schema;
use Pina\Data\SchemaProviderInterface;
use Pina\Html;

class RecordView extends ControlContainer implements InputFactoryInterface, SchemaProviderInterface
{
    use RecordTrait;

    protected RecordFormCompiler $compiler;
    
    public function __construct()
    {
        $this->compiler = $this->makeRecordFormCompiled();
        $this->append($this->compiler);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw(): string
    {
        return Html::nest('div', $this->drawContent(), $this->makeAttributes());
    }

    protected function makeRecordFormCompiled(): RecordFormCompiler
    {
        /** @var RecordFormCompiler $compiler */
        $compiler = App::make(RecordFormCompiler::class);
        $compiler->load($this, $this);

        return $compiler;
    }

    /**
     * @param Field $field
     * @param DataRecord $data
     * @return Control|FormControl
     * @throws \Exception
     */
    public function makeInput(Field $field)
    {
        $name = $field->getName();
        $value = $this->record->getInteractiveValue($name);
        return $this->makeFormStatic()
            ->setName($name)
            ->setTitle($field->getTitle())
            ->setValue($value);
    }

    /**
     * @return FormStatic
     */
    protected function makeFormStatic()
    {
        return App::make(FormStatic::class);
    }

    public function getSchema(): Schema
    {
        return $this->record->getSchema();
    }


}