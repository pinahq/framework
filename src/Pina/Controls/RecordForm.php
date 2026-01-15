<?php

namespace Pina\Controls;

use Pina\App;

use Pina\Data\DataRecord;
use Pina\Data\Field;

use function Pina\__;

/**
 * Форма редактирования
 */
class RecordForm extends HandledForm implements InputFactoryInterface
{
    use RecordTrait;

    protected $formClass = '';

    /** @var ButtonRow */
    protected $buttonRow;

    public function __construct()
    {
        parent::__construct();
        $this->buttonRow = App::make(ButtonRow::class);
        $this->buttonRow->setMain($this->makeSubmit());
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
        $compiler->load($record->getSchema(), $this);

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


}