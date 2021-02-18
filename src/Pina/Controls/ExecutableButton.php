<?php

namespace Pina\Controls;

use Pina\CSRF;

/**
 * Кнопка, инициирующая запрос к серверу
 * @package Pina\Controls
 */
class ExecutableButton extends Button
{

    /**
     * Указать ресурс на сервере, куда отправлять запросы
     * @param string $resource
     * @param string $method
     * @param array $params
     * @return $this
     */
    public function setHandler($resource, $method, $params)
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

}
