<?php

namespace Pina\Controls;

class ActionButton extends LinkedButton
{
    public function __construct()
    {
        $this->addClass('pina-action');
        $this->setLink('#');
    }

    public function setHandler($resource, $method, $params = [])
    {
        $this->setDataAttribute('resource', ltrim($resource, '/'));
        $this->setDataAttribute('method', $method);
        $this->setDataAttribute('params', http_build_query($params));
        $this->addClass('pina-action');
        return $this;
    }

    public function setSuccess($success)
    {
        $this->setDataAttribute('success', $success);
        return $this;
    }

}