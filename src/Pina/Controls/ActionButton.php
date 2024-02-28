<?php

namespace Pina\Controls;

use Pina\CSRF;

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
        $this->setDataAttribute('params', htmlspecialchars(http_build_query($params), ENT_COMPAT));
        $csrfAttributes = CSRF::tagAttributeArray($method);
        if (!empty($csrfAttributes['data-csrf-token'])) {
            $this->setDataAttribute('csrf-token', $csrfAttributes['data-csrf-token']);
        }
        $this->addClass('pina-action');
        return $this;
    }

    public function setSuccess($success)
    {
        $this->setDataAttribute('success', $success);
        return $this;
    }

}