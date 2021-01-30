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
            if ($type instanceof \Closure) {
                $input = $type($data);
            } else {
                $input = $this->resolveTypeAsInput($type);
                $input->setName($field->getKey())
                    ->setTitle($field->getTitle())
                    ->setValue($field->draw($data));
            }

            $form->append($input);
        }

        return $form;
    }

    protected function resolveTypeAsInput($type)
    {
        if (is_array($type)) {
            $input = $this->makeFormSelect();
            $input->setVariants($type);
        } elseif (isset($type[0]) && $type[0] == '/') {
            $resource = substr($type, 1);
            $input = App::make(SelectComponent::class);
            $input->basedOn(\Pina\App::router()->run($resource, 'get'));
        } else {
            $t = App::type($type);
            $input = $t->makeControl();
            $variants = $t->getVariants();
            if (is_array($variants) && count($variants)) {
                $input->setVariants($variants);
            }
        }
        return $input;
    }

    /**
     * @return \Pina\Controls\Form
     */
    protected function makeForm()
    {
        return $this->control(\Pina\Controls\Form::class);
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
