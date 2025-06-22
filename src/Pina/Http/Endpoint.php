<?php

namespace Pina\Http;

use Pina\App;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class Endpoint
{
    private function routerRequest()
    {
        return App::getActualRequest();
    }

    protected function base(): Location
    {
        return $this->routerRequest()->getBaseLocation();
    }

    protected function location(): Location
    {
        return $this->routerRequest()->getLocation();
    }

    /**
     * Request body parameters ($_POST).
     *
     * @return InputBag|ParameterBag
     */
    protected function request()
    {
        return $this->routerRequest()->request;
    }

    /**
     * Query string parameters ($_GET).
     *
     * @return InputBag
     */
    protected function query()
    {
        return $this->routerRequest()->query;
    }

    /**
     * Attribute string parameters.
     *
     * @return ParameterBag
     */
    protected function attributes()
    {
        return $this->routerRequest()->attributes;
    }

    /**
     * @return InputBag
     */
    protected function filters()
    {
        return $this->routerRequest()->filters();
    }

    /**
     * @return InputBag
     */
    protected function context()
    {
        return $this->routerRequest()->context();
    }

    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @return ServerBag
     */
    protected function server()
    {
        return $this->routerRequest()->server;
    }

    /**
     * Uploaded files ($_FILES).
     *
     * @return FileBag
     */
    protected function files()
    {
        return $this->routerRequest()->files;
    }

    /**
     * Cookies ($_COOKIE).
     *
     * @return InputBag
     */
    protected function cookies()
    {
        return $this->routerRequest()->cookies;
    }

    /**
     * Headers (taken from the $_SERVER).
     *
     * @return HeaderBag
     */
    protected function headers()
    {
        return $this->routerRequest()->headers;
    }


}
