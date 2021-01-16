<?php

namespace Pina\Components;

class RecordFormComponent extends RecordData
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
        $form = $this->buildForm()->addClass('pina-form');
        $form->append($this->makeSubmit()->setTitle('Сохранить'));
        $this->append($this->makeCard()->append($form));
    }

    /**
     * @return \Pina\Controls\Form
     */
    protected function buildForm()
    {
        $form = $this->makeForm()
            ->setAction($this->action)
            ->setMethod($this->method);

        $data = $this->getData();

        foreach ($this->schema->getIterator() as $field) {
            $type = $field->getType();
            if ($type == 'static') {
                continue;
            }

            if ($type instanceof \Closure) {
                $input = $type($data);
            } elseif (isset($type[0]) && $type[0] == '/') {
                $resource = substr($type, 1);
                $data = \Pina\App::router()->run($resource, 'get');
                $input = new SelectComponent;
                $input->basedOn($data);
            } else {
                $input = is_array($type) ? $this->makeFormSelect() : $this->makeFormInput();
            }

            $input->setName($field->getKey())
                ->setTitle($field->getTitle())
                ->setValue($field->draw($data));

            if (is_array($type)) {
                $input->setVariants($type);
            }

            $form->append($input);
        }

        return $form;
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

    /**
     * @return \Pina\Controls\FormSelect
     */
    protected function makeFormSelect()
    {
        return $this->control(\Pina\Controls\FormSelect::class);
    }

    /**
     * @return \Pina\Controls\Card
     */
    protected function makeCard()
    {
        return $this->control(\Pina\Controls\Card::class);
    }

    /**
     * @return \Pina\Controls\SubmitButton
     */
    protected function makeSubmit()
    {
        return $this->control(\Pina\Controls\SubmitButton::class);
    }

}
