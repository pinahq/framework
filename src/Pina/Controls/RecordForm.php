<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Html;
use Pina\ResourceManagerInterface;
use Pina\StaticResource\Script;

use function Pina\__;

/**
 * Форма редактирования
 */
class RecordForm extends Form
{
    use RecordTrait;

    protected $formClass = '';

    /** @var ButtonRow */
    protected $buttonRow;

    public function __construct()
    {
        $this->buttonRow = App::make(ButtonRow::class);
        $this->buttonRow->setMain($this->makeSubmit());
        $this->formClass = uniqid('fm');
        $this->addClass($this->formClass);
        $this->addClass('form pina-form');
    }

    public function getButtonRow()
    {
        return $this->buttonRow;
    }

    /**
     * Получить уникальное имя класса тега формы, которое используется для javascript-обработчика
     * @return string
     */
    public function getFormClass()
    {
        return $this->formClass;
    }

    protected function drawInner()
    {
        $content = '';

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

                $description = $field->getDescription();
                if ($description) {
                    $help = new RawHtml();
                    $help->setText(Html::nest('span.help-block text-muted/small', $description));
                    $input->append($help);
                }
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
        return App::make(SubmitButton::class)->setTitle(__('Сохранить'));
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