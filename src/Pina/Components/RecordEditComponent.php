<?php

namespace Pina\Components;

use Pina\Controls\FormInput;
use Pina\Controls\Form;
use Pina\Controls\CSRFHidden;

class RecordEditComponent extends RecordData //implements ComponentInterface
{

    protected $method = 'GET';
    protected $action = null;

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function build()
    {
        $form = $this->makeForm()
            ->setAction($this->action)
            ->setMethod($this->method);

        $r = '';
        $controls = [];
        $data = $this->getData();
        foreach ($this->schema as $field) {
            $title = $field->getTitle();
            $key = $field->getKey();
            $value = $field->draw($data);

            $form->append(
                $this->makeFormInput()->setName($key)->setTitle($title)->setValue($value)
            );
        }

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
