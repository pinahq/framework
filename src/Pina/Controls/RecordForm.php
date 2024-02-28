<?php

namespace Pina\Controls;

use Pina\App;

use Pina\Data\Field;

use function Pina\__;

/**
 * Форма редактирования
 */
class RecordForm extends HandledForm
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
        $content = parent::drawInner();

        $data = $this->record->getData();
        $groupedSchema = clone $this->record->getSchema();
        $groupedSchema->forgetHiddenStatic();
        foreach ($groupedSchema->getGroupIterator() as $schema) {
            if ($schema->isEmpty()) {
                continue;
            }
            $card = $this->makeCard()->setTitle($schema->getTitle());

            $description = $schema->getDescription();
            if ($description) {
                /** @var Paragraph $p */
                $p = App::make(Paragraph::class);
                $p->setText($description);
                $card->append($p);
            }

            $inputs = [];
            $widthGained = 0;
            $widthLimit = 12;
            foreach ($schema->getIterator() as $field) {
                $width = $field->getWidth();
                if ($widthGained + $width > $widthLimit) {
                    $this->flushInputs($card, $inputs);
                    $widthGained = 0;
                    $inputs = [];
                }

                $inputs[] = $this->makeInput($field, $data);
                $widthGained += $width;
            }
            $this->flushInputs($card, $inputs);

            $content .= $card;
        }

        return $content;
    }

    /**
     * @param Field $field
     * @param array $data
     * @return Control|FormControl
     * @throws \Exception
     */
    protected function makeInput(Field $field, array $data)
    {
        $type = $field->getType();
        $name = $field->getName();
        $value = isset($data[$name]) ? $data[$name] : null;
        return App::type($type)->setContext($data)->makeControl($field, $value);
    }

    /**
     * @param Card $card
     * @param Control[] $inputs
     */
    protected function flushInputs(Card $card, $inputs)
    {
        if (count($inputs) > 1) {
            $row = $this->makeRow();
            foreach ($inputs as $input) {
                $row->append($input);
            }
            $card->append($row);
            return;
        }

        foreach ($inputs as $input) {
            $card->append($input);
        }
    }

    protected function drawFooter()
    {
        return parent::drawFooter() . $this->buttonRow;
    }

    /**
     * @return Card
     */
    protected function makeCard()
    {
        return App::make(Card::class);
    }

    /**
     * @return Control
     */
    protected function makeRow(): Control
    {
        return App::make(FormRow::class);
    }


    /**
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return App::make(SubmitButton::class)->setTitle(__('Сохранить'));
    }


}