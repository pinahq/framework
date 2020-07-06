<?php

namespace Pina;

class Router
{

    protected $endpoints = [];
    protected $patterns = [];

    public function register($pattern, $class)
    {
        $controller = Url::controller($pattern);
        $this->endpoints[$controller] = $class;
        $this->patterns[$controller] = $pattern;
    }

    /**
     * 
     * @param string $resource
     * @param string $method
     * @param array $data
     * @return Components\DataObject
     * @throws Container\NotFoundException
     */
    public function run($resource, $method, $data = [])
    {
        list($controller, $action, $params) = Url::route($resource, $method);

        $c = $this->base($controller);
        if ($c === null) {
            throw new Container\NotFoundException;
        }

        $cl = $this->endpoints[$c];
        $inst = new $cl;

        $pattern = $this->patterns[$c];
        $parsed = [];
        $this->parse($resource, $pattern, $parsed);
        $params = array_merge($parsed, $params);

        $deeper = [];
        if ($this->parse($resource, $pattern . "/:id/:__action", $deeper)) {
            $action .= $this->ucfirstEveryWord($deeper['__action']);
        }

        return $inst->$action(array_merge($data, $params))->setMeta('location', $resource);
    }

    public function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty($this->endpoints[$controller])) {
            return $controller;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i + 1));
            if (isset($this->endpoints[$c])) {
                return $c;
            }
        }
        return null;
    }

    public function parse($resource, $pattern, &$parsed)
    {
        list($preg, $map) = Url::preg($pattern);
        return $this->pregParse($resource, $preg, $map, $parsed);
    }

    public function pregParse($resource, $preg, $map, &$parsed)
    {
        $parsed = [];
        if (preg_match("/^" . $preg . "/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
            return true;
        }
        return false;
    }

    private function ucfirstEveryWord($s)
    {
        $parts = preg_split("/[^\w]/s", $s);
        foreach ($parts as $k => $v) {
            $parts[$k] = ucfirst($v);
        }
        return implode($parts);
    }

}
