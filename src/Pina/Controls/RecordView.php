<?php

namespace Pina\Controls;

use Pina\Data\DataRecord;
use Pina\App;

class RecordView extends Control
{
    use RecordTrait;

    protected function draw()
    {
        $data = $this->record->getHtmlData();

        $content = '';
        foreach ($this->record->getSchema()->getGroupIterator() as $schema) {
            if ($schema->isEmpty()) {
                continue;
            }
            $container = $this->makeCard()->setTitle($schema->getTitle());
            foreach ($schema->getIterator() as $field) {
                $title = $field->getTitle();
                $key = $field->getKey();
                $value = $data[$key];

                $static = $this->makeFormStatic()
                    ->setName($key)
                    ->setTitle($title)
                    ->setValue($value);
                $container->append($static);
            }
            $content .= $container;
        }

        return $content;
    }

    /**
     * @return Card
     */
    protected function makeCard()
    {
        return App::make(Card::class);
    }

    /**
     * @return FormStatic
     */
    protected function makeFormStatic()
    {
        return App::make(FormStatic::class);
    }


}