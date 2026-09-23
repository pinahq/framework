<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Components\ButtonRow;
use Pina\Controls\Control;
use Pina\Controls\Form\FormControl;
use Pina\Controls\Form\HandledForm;
use Pina\Controls\Form\InputFactoryInterface;
use Pina\Controls\Form\SubmitButton;
use Pina\Data\DataRecord;
use Pina\Data\Field;
use Pina\Data\Schema;
use Pina\Data\SchemaProviderInterface;
use function Pina\__;

/**
 * Форма редактирования
 */
class RecordForm extends HandledForm implements InputFactoryInterface, SchemaProviderInterface
{
    use RecordTrait;

    protected $formClass = '';

    protected RecordFormCompiler $compiler;

    /** @var ButtonRow */
    protected $buttonRow;

    public function __construct()
    {
        parent::__construct();
        $this->buttonRow = App::make(ButtonRow::class);
        $this->buttonRow->setMain($this->makeSubmit());
        $this->compiler = $this->makeRecordFormCompiled();
        $this->append($this->compiler);
    }

    public function __clone()
    {
        $this->buttonRow = clone $this->buttonRow;
    }

    /**
     * @return ButtonRow
     */
    public function getButtonRow()
    {
        return $this->buttonRow;
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
        $type = $field->getType();
        $name = $field->getName();
        return App::type($type)->setContext($this->record->getData())->makeControl($field, $this->record->getValue($name));
    }

    protected function drawFooter()
    {
        return parent::drawFooter() . $this->buttonRow;
    }

    /**
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return App::make(SubmitButton::class)->setTitle(__('Сохранить'));
    }

    public function getSchema(): Schema
    {
        return $this->record->getSchema();
    }


}