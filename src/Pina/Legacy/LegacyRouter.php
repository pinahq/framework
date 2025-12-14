<?php

namespace Pina\Legacy;

use Pina\ModuleInterface;
use Pina\Url;

class LegacyRouter
{

    protected $layout = '';
    protected $owners = [];

    public function own($controller, $module)
    {
        $controller = trim($controller, "/");
        $this->owners[$controller] = $module;
    }

    public function ownList(ModuleInterface $module, array $controllerList)
    {
        foreach ($controllerList as $controller) {
            $this->own($controller, $module);
        }
    }

    public function owner($controller): ?ModuleInterface
    {
        $c = $this->base($controller);
        if ($c === null) {
            return null;
        }
        return $this->owners[$c];
    }

    protected function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty($this->owners[$controller])) {
            return $controller;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i+1));
            if (isset($this->owners[$c])) {
                return $c;
            }
        }
        return null;
    }

    public function exists($resource, $method)
    {
        list($controller, $action, $parsed) = Url::route($resource, $method);
        return !is_null($this->base($controller));
    }

    public function run($resource, $method, $data)
    {
        $handler = new RequestHandler($resource, $method, $data);

        $defaultLayout = $this->getDefaultLayout();
        if ($defaultLayout) {
            $handler->setLayout($defaultLayout);
        }

        Request::push($handler);
        $response = Request::run();
        return $response;
//        $response->send();
    }


    /**
     * Задает шаблон layout`а по умолчанию
     * @param string $layout Имя шаблона layout`а
     */
    public function setDefaultLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Возвращает шаблон layout`а по умолчанию
     * @return string
     */
    public function getDefaultLayout()
    {
        return $this->layout;
    }


}