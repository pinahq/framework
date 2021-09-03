<?php

namespace Pina\Components;

use Pina\Controls\Card;
use Pina\Controls\FormStatic;

/**
 * @deprecated см \Pina\Controls\RecordView
 */
class RecordViewComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $data = $this->getHtmlData();

        foreach ($this->schema->getGroupIterator() as $schema) {
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
            $this->append($container);
        }
    }

    /**
     * @return Card
     */
    protected function makeCard()
    {
        return $this->control(Card::class);
    }

    /**
     * @return FormStatic
     */
    protected function makeFormStatic()
    {
        return $this->control(FormStatic::class);
    }

}
