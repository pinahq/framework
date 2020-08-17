<?php

namespace Pina\Components;

use Pina\Controls\FormInput;
use Pina\Controls\Form;
use Pina\Controls\CSRFHidden;

class RecordEditComponent extends RecordData //implements ComponentInterface
{

    protected $controls = [];

    public function build()
    {
        $fields = $this->schema->getFields();
        $titles = $this->schema->getTitles();

        $method = 'PUT';
        $form = Form::instance()
            ->setAction($this->getMeta('location'))
            ->setMethod($method);

        $r = '';
        $controls = [];
        foreach ($fields as $field) {
            $title = $this->schema->getTitle($field);
            $type = $this->schema->getType($field);
            $value = $this->data[$field] ? $this->data[$field] : '';

            $form->append(
                FormInput::instance()->setTitle($title)->setValue($value)
            );
        }

        $form->append(CSRFHidden::instance()->setMethod($method));
        
        $this->append($form);
    }

}
