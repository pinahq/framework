<?php

namespace Pina\Controls;

use Pina\Html;
use Pina\Controls\Control;

class Form extends Control
{

    protected $action = '';
    protected $method = '';

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function makeOptions()
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

        return ['method' => $method, 'action' => $action, 'class' => $this->makeClass('form pina-form')];
    }

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

    public function draw()
    {
        $csrf = \Pina\CSRF::formField($this->method);

        return Html::tag('form', $csrf . $this->compile(), $this->makeOptions());
    }

}
