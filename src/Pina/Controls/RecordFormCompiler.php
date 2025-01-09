<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;

class RecordFormCompiler extends Control
{

    protected $record;
    protected $factory;

    public function load(DataRecord $record, InputFactoryInterface $factory)
    {
        $this->record = $record;
        $this->factory = $factory;
    }

    protected function draw()
    {
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

                $inputs[] = $this->factory->makeInput($field, $this->record);
                $widthGained += $width;
            }
            $this->flushInputs($card, $inputs);

            $content .= $card;
        }
        return $content;
    }

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

}