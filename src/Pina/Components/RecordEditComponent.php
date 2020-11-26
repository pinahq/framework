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
        $method = 'PUT';
        $form = $this->makeForm()
            ->setAction($this->getMeta('location'))
            ->setMethod($method);

        $r = '';
        $controls = [];
        foreach ($this->schema as $field) {
            $title = $field->getTitle();
            $key = $field->getKey();
            $value = $field->draw($this->data);

            $form->append(
                $this->makeFormInput()->setName($key)->setTitle($title)->setValue($value)
            );
        }

        $form->append(CSRFHidden::instance()->setMethod($method));

        $this->append($form);
    }

    /**
     * @return \Pina\Controls\Form
     */
    protected function makeForm()
    {
        return $this->control(\Pina\Controls\Form::class);
    }

    /**
     * @return \Pina\Controls\FormInput
     */
    protected function makeFormInput()
    {
        return $this->control(\Pina\Controls\FormInput::class);
    }

}
