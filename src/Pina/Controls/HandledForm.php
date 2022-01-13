<?php


namespace Pina\Controls;


use Pina\App;
use Pina\ResourceManagerInterface;
use Pina\StaticResource\Script;

class HandledForm extends Form
{

    protected $formClass = '';

    public function __construct()
    {
        $this->formClass = uniqid('fm');
        $this->addClass($this->formClass);
        $this->addClass('form pina-form');
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
        $this->makeScript();

        return parent::drawInner();
    }

    protected function makeScript()
    {
        $this->resources()->append(
            (new Script())->setContent(
                '<script>$(".' . $this->formClass . '").on("success", function(event, packet, status, xhr) {if (!PinaRequest.handleRedirect(xhr)) {var target = $(this).attr("data-success") ? $(this).attr("data-success") : document.location.pathname; document.location = target + "?changed=" + Math.random(); }});</script>'
            )
        );
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