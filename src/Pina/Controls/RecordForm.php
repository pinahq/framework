<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\ResourceManagerInterface;
use Pina\StaticResource\Script;

class RecordForm extends Form
{
    use RecordTrait;

    protected $formClass = '';

    /** @var ButtonRow */
    protected $buttonRow;

    public function __construct()
    {
        $this->buttonRow = App::make(ButtonRow::class);
        $mainButton = $this->makeSubmit()->setTitle('Сохранить');
        $this->buttonRow->setMain($mainButton);
        $this->formClass = uniqid('fm');
        $this->addClass($this->formClass);
        $this->addClass('form pina-form');
    }

    public function getFormClass()
    {
        return $this->formClass;
    }

    protected function compile()
    {
        $content = parent::compile();

        $data = $this->record->getData();
        foreach ($this->record->getSchema()->getGroupIterator() as $schema) {
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

            $content .= $card;
        }

        $content .= $this->buttonRow;

        $this->resources()->append(
            (new Script())->setContent(
                '<script>$(".' . $this->formClass . '").on("success", function(event, packet, status, xhr) {if (!PinaRequest.handleRedirect(xhr)) {var target = $(this).attr("data-success") ? $(this).attr("data-success") : document.location.pathname; document.location = target + "?changed=" + Math.random(); }});</script>'
            )
        );

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
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return App::make(SubmitButton::class);
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