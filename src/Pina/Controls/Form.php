<?php

namespace Pina\Controls;

use Pina\CSRF;
use Pina\Html;

/**
 * HTML-форма
 * @package Pina\Controls
 */
class Form extends Control
{

    protected $action = '';
    protected $method = 'get';

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    protected function draw()
    {
        $csrf = CSRF::formField($this->method);

        return Html::tag('form', $csrf . $this->compile(), $this->makeAttributes());
    }

    /**
     * @return array
     */
    protected function makeAttributes($attributes = [])
    {
        $method = strtolower($this->method);
        $parsed = parse_url($this->action);

        if ($method != 'get' && $method != 'post') {
            $parsed['path'] = '/' . $method . '!' . ltrim($parsed['path'], '/');
        }

        if ($method != 'get') {
            $method = 'post';
        }

        $action = $this->buildParsed($parsed);

        $attributes = array_merge(['method' => $method, 'action' => $action], $attributes);

        return parent::makeAttributes($attributes);
    }

    /**
     * @param array $parsed_url
     * @return string
     */
    protected function buildParsed($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}
