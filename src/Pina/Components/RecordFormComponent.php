<?php

namespace Pina\Components;

use Pina\App;
use Pina\Controls\ButtonRow;
use Pina\Controls\Card;
use Pina\Controls\Form;
use Pina\Controls\SubmitButton;
use Pina\ResourceManagerInterface;
use Pina\StaticResource\Script;

/**
 * Форма для редактирования записи
 */
class RecordFormComponent extends RecordData
{

    protected $method = 'GET';
    protected $action = null;
    protected $formClass = '';

    /** @var ButtonRow */
    protected $buttonRow;

    public function __construct()
    {
        $this->buttonRow = App::make(ButtonRow::class);
        $this->formClass = uniqid('fm');
    }

    /**
     * Настраивает HTTP-метод для отправки формы
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Настраивает HTTP-обработчик формы
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Получить ссылку на строчку с кнопками под формой
     * @return ButtonRow
     */
    public function getButtonRow()
    {
        return $this->buttonRow;
    }

    public function getFormClass()
    {
        return $this->formClass;
    }

    public function build()
    {
        $form = $this->buildForm()->addClass('form')->addClass('pina-form');
        $mainButton = $this->makeSubmit()->setTitle('Сохранить');
        $this->buttonRow->setMain($mainButton);
        $this->append($form);
    }

    /**
     * @return Form
     */
    protected function buildForm()
    {
        $form = $this->makeForm()
            ->setAction($this->action)
            ->setMethod($this->method);

        $data = $this->getData();
        foreach ($this->schema->getGroupIterator() as $schema) {
            if ($schema->isEmpty()) {
                continue;
            }
            $card = $this->makeCard()->setTitle($schema->getTitle());

            foreach ($schema->getIterator() as $field) {
                $type = $field->getType();
                $name = $field->getKey();
                $value = isset($data[$name]) ? $data[$name] : null;
                $input = App::type($type)->setContext($data)->makeControl($field, $value);

                $card->append($input);
            }

            $form->append($card);
        }

        $form->append($this->buttonRow);

        $form->addClass($this->formClass);

        $this->resources()->append(
            (new Script())->setContent(
                '<script>$(".' . $this->formClass . '").on("success", function(event, packet, status, xhr) {if (!PinaRequest.handleRedirect(xhr)) {var target = $(this).attr("data-success") ? $(this).attr("data-success") : document.location.pathname; document.location = target + "?changed=" + Math.random(); }});</script>'
            )
        );

        return $form;
    }

    /**
     * @return Form
     */
    protected function makeForm()
    {
        return $this->control(Form::class);
    }

    /**
     * @return Card
     */
    protected function makeCard()
    {
        return $this->control(Card::class);
    }

    /**
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return $this->control(SubmitButton::class);
    }

    /**
     *
     * @return ResourceManagerInterface
     */
    protected function resources()
    {
        return App::container()->get(ResourceManagerInterface::class);
    }

}
