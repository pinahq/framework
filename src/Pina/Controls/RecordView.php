<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\Field;

class RecordView extends Control
{
    use RecordTrait;

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function drawInner()
    {
        $data = $this->record->getInteractiveData();

        $content = '';

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
        $title = $field->getTitle();
        $key = $field->getName();
        $value = $data[$key];
        return $this->makeFormStatic()
            ->setName($key)
            ->setTitle($title)
            ->setValue($value);
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
     * @return FormStatic
     */
    protected function makeFormStatic()
    {
        return App::make(FormStatic::class);
    }


}