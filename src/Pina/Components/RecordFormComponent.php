<?php

namespace Pina\Components;

use Pina\App;

/**
 * Форма для редактирования записи
 */
class RecordFormComponent extends RecordData
{

    protected $method = 'GET';
    protected $action = null;

    /**
     * Настраивает HTTP-метод для отправки формы
     * @param type $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Настраивает HTTP-обработчик формы
     * @param type $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function build()
    {
        $form = $this->buildForm()->addClass('form')->addClass('pina-form');
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
            $name = $field->getKey();
            $value = isset($data[$name]) ? $data[$name] : null;
            $input = App::type($type)->makeControl($field, $value);

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
