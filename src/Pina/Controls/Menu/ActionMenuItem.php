<?php


namespace Pina\Controls\Menu;


use Pina\Access;
use Pina\CSRF;
use Pina\Html;

class ActionMenuItem extends MenuItem
{
    protected $title = '';
    protected $resource = '';

    public function load(string $title, string $resource, string $method, array $params = [])
    {
        $this->title = $title;
        $this->resource = $resource;

        $this->setDataAttribute('resource', ltrim($resource, '/'));
        $this->setDataAttribute('method', $method);
        $this->setDataAttribute('params', http_build_query($params));
        $csrfAttributes = CSRF::tagAttributeArray($method);
        if (!empty($csrfAttributes['data-csrf-token'])) {
            $this->setDataAttribute('csrf-token', $csrfAttributes['data-csrf-token']);
        }
        $this->addClass('pina-action');
    }

    protected function isPermitted(): bool
    {
        return Access::isPermitted($this->resource);
    }

    protected function drawInner()
    {
        return Html::a($this->title, '#', $this->makeAttributes());
    }

}