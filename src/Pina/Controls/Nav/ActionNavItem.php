<?php


namespace Pina\Controls\Nav;

use Pina\Access;
use Pina\CSRF;

class ActionNavItem extends LinkNavItem
{
    protected $resource = '';
    protected $method = '';
    protected $params = [];

    public function load(string $title, string $resource, string $method = 'get', array $params = [])
    {
        $this->title = $title;
        $this->link = '#';
        $this->resource = $resource;
        $this->method = $method;
        $this->params = $params;
    }

    protected function makeLinkAttributes()
    {
        $options = parent::makeLinkAttributes();
        $options['data-resource'] = ltrim($this->resource, '/');
        $options['data-method'] = $this->method;
        $options['data-params'] = http_build_query($this->params);
        $csrfAttributes = CSRF::tagAttributeArray($this->method);
        if (!empty($csrfAttributes['data-csrf-token'])) {
            $options['data-csrf-token'] = $csrfAttributes['data-csrf-token'];
        }
        $options['class'] = trim(($options['class'] ?? '') . ' pina-action');
        return $options;
    }

    protected function isPermitted(): bool
    {
        return Access::isPermitted($this->resource);
    }

}