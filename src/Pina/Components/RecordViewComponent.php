<?php

namespace Pina\Components;

use Pina\App;
use Pina\Controls\Card;
use Pina\Controls\FormStatic;

class RecordViewComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $data = $this->getData();


        foreach ($this->schema->getGroupIterator() as $schema) {
            if ($schema->isEmpty()) {
                continue;
            }
            $container = $this->makeCard()->setTitle($schema->getTitle());
            foreach ($schema->getIterator() as $field) {
                $title = $field->getTitle();
                $key = $field->getKey();
                $value = $field->draw($data);

                $type = App::type($field->getType());

                $static = $this->makeFormStatic()
                    ->setName($key)
                    ->setTitle($title)
                    ->setValue($type->format($value));
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
