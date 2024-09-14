<?php


namespace Pina\Controls\Nav;

use Pina\Access;
use Pina\CSRF;

class ActionNavItem extends LinkNavItem
{
    protected $resource = '';

    public function load(string $title, string $resource, string $method = 'get', array $params = [])
    {
        $this->title = $title;
        $this->link = '#';
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

}