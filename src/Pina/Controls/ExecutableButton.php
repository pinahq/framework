<?php

namespace Pina\Controls;

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
        $this->setDataAttribute('params', http_build_query($params));
        $this->addClass('pina-action');
        return $this;
    }

}
