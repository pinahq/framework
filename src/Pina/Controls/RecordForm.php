<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Html;

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
        foreach ($this->record->getSchema()->getGroupIterator() as $schema) {
            if ($schema->isEmpty()) {
                continue;
            }
            $card = $this->makeCard()->setTitle($schema->getTitle());
            foreach ($schema->getIterator() as $field) {
                $type = $field->getType();
                $name = $field->getKey();
                $value = isset($data[$name]) ? $data[$name] : null;
                $input = App::type($type)->setContext($data)->makeControl($field, $value);

                $description = $field->getDescription();
                if ($description) {
                    $help = new RawHtml();
                    $help->setText(Html::nest('span.help-block text-muted/small', $description));
                    $input->append($help);
                }
                $card->append($input);
            }

            $content .= $card;
        }

        return $content;
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
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return App::make(SubmitButton::class)->setTitle(__('Сохранить'));
    }


}